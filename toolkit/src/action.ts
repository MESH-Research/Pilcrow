/// <reference types="vite/client" />
import * as core from "@actions/core";
import type { ActionCommand, ActionStage } from "types.ts";

const getCommandFile = (command: string): string => `../commands/${command}.ts`;

const commands = import.meta.glob("../commands/*.ts", {
    import: "runCommand",
});

export async function run(stage: ActionStage): Promise<void> {
    try {
        const command: string = core.getInput("command");
        const commandFile = getCommandFile(command);

        if (!(commandFile in commands)) {
            throw new Error(`Command "${command}" not found.`);
        }
        core.debug(`Running ${command} in stage: ${stage}`);
        commands[commandFile]().then((mod: unknown) => {
            return (mod as ActionCommand)(stage);
        });
    } catch (error) {
        if (error instanceof Error) {
            core.setFailed(error.message);
        }
    }
}
