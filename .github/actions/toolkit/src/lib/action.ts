/// <reference types="vite/client" />
import { debug, getInput, setFailed } from "@actions/core";
import type { ActionCommand, ActionDefinition, ActionStage } from "types.ts";

const getCommandFile = (command: string): string => `../commands/${command}.ts`;

const commands = import.meta.glob("../commands/*.ts", {
    import: "runCommand",
});

export async function run(stage: ActionStage): Promise<void> {
    try {
        const command: string = getInput("command");
        const commandFile = getCommandFile(command);

        if (!(commandFile in commands)) {
            throw new Error(`Command "${command}" not found.`);
        }
        debug(`Running ${command} in stage: ${stage}`);
        commands[commandFile]().then((mod: unknown) => {
            return (mod as ActionCommand)(stage);
        });
    } catch (error) {
        if (error instanceof Error) {
            setFailed(error.message);
        }
    }
}

export function runCommand(definition: ActionDefinition): ActionCommand {
    return function (stage: ActionStage): Promise<void> {
        return !!definition[stage] ? definition[stage]() : Promise.resolve();
    };
}
