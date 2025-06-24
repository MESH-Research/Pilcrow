import * as glob from "@actions/glob";
import * as fs from "fs/promises";
import * as core from "@actions/core";
import { AnsiUp } from "ansi_up";

type OutputDetails = {
    service: string;
    output: string;
    ext: SupportedExtensions;
};

type SupportedExtensions = keyof typeof summaryProcessors;

export async function generateSummary(path: string) {
    const globPattern = `${path}/*/*`;
    core.debug("Searching for files with pattern: " + globPattern);
    const globber = await glob.create(globPattern);
    for await (const file of globber.globGenerator()) {
        core.debug("Found file: " + file);
        const fileStat = await fs.stat(file);
        if (!fileStat.isFile()) {
            core;
            continue;
        }
        if (fileStat.size <= 0) {
            core.debug("Skipping empty file: " + file);
            continue;
        }
        try {
            await fs.access(file, fs.constants.R_OK);
        } catch (err) {
            core.debug("Skipping unreadable file: " + file);
            continue;
        }

        const outputDetails =
            /(?<service>[^\/]+)\/(?<output>[^\/]+)\.(?<ext>[^\.]+)$/.exec(file)
                ?.groups ?? { service: null, output: null, ext: null };
        if (
            !outputDetails.service ||
            !outputDetails.output ||
            !outputDetails.ext
        ) {
            core.debug("Skipping file with unexpected name: " + file);
            continue;
        }
        if (!(outputDetails.ext in summaryProcessors)) {
            core.debug("Skipping file with unsupported extension: " + file);
            continue;
        }
        const { service, output, ext } = outputDetails as OutputDetails;

        core.debug(
            `Processing file: service=${service}, output=${output}, ext=${ext}`,
        );

        core.summary.addDetails(output, await summaryProcessors[ext](file));
    }
}

const summaryProcessors = {
    txt: async function (file: string) {
        const ansi_up = new AnsiUp();
        ansi_up.escape_html = false;
        core.debug("Converting ANSI to HTML for file: " + file);
        //Load file contents into variable
        const content = await fs.readFile(file);

        return ansi_up.ansi_to_html(content.toString());
    },
    md: async function (file: string) {
        core.debug("Processing Markdown file: " + file);
        return (await fs.readFile(file)).toString();
    },
};
