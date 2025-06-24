/// <reference types="vite/client" />
import { debug, setFailed } from "@actions/core";
import type { ActionCommand, ActionStage, ActionInputs } from "types.ts";

const getCommandFile = (command: string): string => `../commands/${command}.ts`;

const commands = import.meta.glob("../commands/*.ts", {
    import: "runCommand",
});

export async function run(
    stage: ActionStage,
    inputs: ActionInputs,
): Promise<void> {
    try {
        const { command } = inputs;
        const commandFile = getCommandFile(command);

        if (!(commandFile in commands)) {
            throw new Error(`Command "${command}" not found.`);
        }
        debug(`Running ${command} in stage: ${stage}`);
        commands[commandFile]().then((mod: unknown) => {
            return (mod as ActionCommand)(stage, inputs);
        });
    } catch (error) {
        if (error instanceof Error) {
            setFailed(error.message);
        }
    }
}
