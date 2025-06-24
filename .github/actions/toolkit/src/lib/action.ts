import { ActionCommand, ActionDefinition, ActionStage } from "/src/types";

export function runCommand(definition: ActionDefinition): ActionCommand {
    return function (stage: ActionStage): Promise<void> {
        return !!definition[stage] ? definition[stage]() : Promise.resolve();
    };
}
