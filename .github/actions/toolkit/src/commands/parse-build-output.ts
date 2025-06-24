import * as core from "@actions/core";
import * as fs from "node:fs/promises";
import { tmpdir } from "node:os";
import { sep } from "path";
import { runCommand } from "/lib/action";
import { getConfigValue } from "/lib/config";
import { getCommandOutput } from "/lib/tools";
import { DefaultArtifactClient } from "@actions/artifact";
import { dirname } from "node:path";
export { command as runCommand };

const command = runCommand({
    post: async function () {
        const frontendBundle = core.getState("frontendBundle");
        const frontendImage = core.getState("frontendImage");
        if (!frontendBundle || !frontendImage) {
            core.info("No frontend bundle to attach.");
            return;
        }

        core.info("Uploading frontend bundle as GHA artifact...");
        const artifact = new DefaultArtifactClient();

        await artifact
            .uploadArtifact(
                "frontend-bundle",
                [frontendBundle],
                dirname(frontendBundle),
            )
            .then(({ size, id }) => {
                core.info(
                    `Uploaded frontend bundle as artifact, id: ${id}, size: ${size}`,
                );
            })
            .catch((reason: unknown) => {
                core.error("Failed to create GHA artifact.");
                core.setFailed(
                    "Failed to upload frontend bundle as GHA artifact: " +
                        reason,
                );
            });

        const artifactType = getConfigValue("oras-bundle-type");
        const actor = getConfigValue("oras-actor");
        const token = getConfigValue("token");

        try {
            await getCommandOutput("docker", [
                "manifest",
                "inspect",
                frontendImage,
            ]);
        } catch {
            core.info("Frontend image not found in registry: " + frontendImage);
            return;
        }
        core.info("Attaching frontend bundle to image: " + frontendImage);
        const orasLoginOpts: string[] = [];
        orasLoginOpts
            .concat(!!actor ? ["--username", actor] : [])
            .concat(!!token ? ["--password", token] : []);

        await getCommandOutput("oras", orasLoginOpts).catch(
            (error: unknown) => {
                core.error("Failed to login to registry.");
                core.setFailed("ORAS Failed to login to registry");
                throw error;
            },
        );

        await getCommandOutput("oras", [
            "attach",
            frontendImage,
            "--disable-path-validation",
            "--artifact-type",
            artifactType,
            frontendBundle,
        ]).catch((error: unknown) => {
            core.error("Failed to attach bundle to image.");
            core.setFailed("ORAS Failed to attach bundle to image");
            throw error;
        });
    },
    main: async function () {
        parseDockerMeta(core.getInput("docker-metadata"));

        const outputCachePath = getConfigValue("output-cache-path");
        await extractOutputCache(outputCachePath);

        //Check if a frontend-bundle was written to the output cache.
        const bundlePath = `${outputCachePath}/web-build/frontend-bundle.tar.gz`;

        try {
            fs.access(bundlePath, fs.constants.R_OK);
        } catch (err) {
            core.debug("No frontend bundle found at: " + bundlePath);
            core.info("No frontend bundle found.");
            return;
        }
        core.saveState("frontendBundle", bundlePath);
        core.setOutput("frontend-bundle", bundlePath);
    },
});

function parseDockerMeta(bakeMetaOutput: string) {
    const meta = JSON.parse(bakeMetaOutput).catch((reason: unknown) =>
        core.error("core.error parsing docker bake metadata: " + reason),
    );
    if (!meta) {
        return;
    }
    const webTarget = Object.keys(meta).find((key) => key.endsWith("web"));
    const webImage = webTarget ? meta[webTarget].image.name : null;
    core.saveState("frontendImage", webImage);
    core.saveState("webTarget", webTarget);
}

async function extractOutputCache(cachePath: string) {
    const dockerBuildDir = await fs.mkdtemp(`${tmpdir()}${sep}output-cache-`);
    const dockerfile = "./.github/actions/toolkit/Dockerfile.extract-cache";
    //Generate a timestamp to use to prevent docker from caching
    const buildStamp = new Date().toISOString();

    await getCommandOutput("docker", [
        "buildx",
        "build",
        "-f " + dockerfile,
        "--tag output:extract",
        "--build-arg BUILDSTAMP=" + buildStamp,
        "--load",
        dockerBuildDir,
    ]);
    core.info("Building cache extractor image...");
    await getCommandOutput("docker", ["rm", "-f", "cache-container"]);
    core.info("Creating cache extractor...");
    await getCommandOutput("docker", [
        "create",
        "-ti",
        "--name cache-container",
        "output:extract",
    ]);
    core.info("Copying cache from extractor...");
    await getCommandOutput("docker", [
        "cp",
        "-L",
        "cache-container:/var/.output-cache",
        cachePath,
    ]);
}
