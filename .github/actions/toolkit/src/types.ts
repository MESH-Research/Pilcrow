export type ActionStage = "pre" | "main" | "post";

export type ActionCommand = (
    stage: ActionStage,
    inputs: ActionInputs,
) => Promise<void>;

export type ActionInputs = {
    "docker-metadata": string;
    "bake-files": string;
    target: string;
    command: string;
};

export interface ActionDefinition {
    pre?: () => Promise<void>;
    main: () => Promise<void>;
    post?: () => Promise<void>;
}

export interface ActionCommandModule {
    runCommand: ActionCommand;
}
