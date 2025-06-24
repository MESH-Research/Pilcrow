import * as exec from "@actions/exec";

export async function getCommandOutput(
    ...args: Parameters<typeof exec.getExecOutput>
): Promise<string> {
    const output = await exec.getExecOutput(...args);
    if (output.exitCode !== 0) {
        throw new Error(
            `Command failed: ${args?.join(" ")}, exit code: ${output.exitCode}`,
        );
    }
    return output.stdout;
}
