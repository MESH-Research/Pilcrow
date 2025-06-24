import { runCommand } from "/lib/action";
import * as core from "@actions/core";
import * as fs from "node:fs/promises";
import { tmpdir } from "os";
import { sep } from "path";
import { DefaultArtifactClient } from "@actions/artifact";
import { cp } from "@actions/io";
import { context } from "@actions/github";

export { command as runCommand };

const command = runCommand({
    pre: async function () {},
    post: async function () {
        if (core.isDebug()) {
            const artifact = new DefaultArtifactClient();

            const bakeFiles = core.getState("bakeFiles");
            core.debug("Bake files: " + bakeFiles);
            const files = bakeFiles.split(/\s*/);
            core.debug("Uploading bake files as artifact...");
            const tmpPath = await fs.mkdtemp(`${tmpdir()}${sep}-bake-`);
            core.debug("Created temporary directory: " + tmpPath);
            for (const file of files) {
                cp(file, tmpPath)
                    .catch((reason) =>
                        core.error(
                            `Failed to copy bake file: ${file} (${reason})`,
                        ),
                    )
                    .then(() => core.debug("Copied bake file " + file));
            }

            await artifact
                .uploadArtifact(`${context.job}-bake-files`, files, tmpPath)
                .then(({ size, id }) =>
                    core.debug(
                        `Uploaded bake files as artifact, id: ${id}, size: ${size}`,
                    ),
                )
                .catch((reason) =>
                    core.error(
                        "Failed to upload bake files as artifact: " + reason,
                    ),
                );
            core.debug("Cleaning up temporary directory: " + tmpPath);
            await fs.rm(tmpPath, { recursive: true, force: true });
        }
    },
    main: async function () {
        core.debug("Saving bake file names to upload as artifacts...");
        core.saveState("bakeFiles", core.getInput("bake-files"));
    },
});
