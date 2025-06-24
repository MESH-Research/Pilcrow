import { debug, getInput, setFailed } from "@actions/core";
import type { ActionCommand, ActionDefinition, ActionStage } from "types.ts";

export async function run(stage: ActionStage): Promise<void> {
    try {
        const command: string = getInput("command");

        debug(`Running ${command} in stage: ${stage}`);

        await import(`../commands/${command}.ts`).then((module) => {
            return module.runCommand(stage);
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
