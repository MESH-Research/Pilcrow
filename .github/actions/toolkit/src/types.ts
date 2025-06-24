export type ActionStage = "pre" | "main" | "post";

export type ActionCommand = (stage: ActionStage) => Promise<void>;

export interface ActionDefinition {
    pre?: () => Promise<void>;
    main: () => Promise<void>;
    post?: () => Promise<void>;
}

export interface ActionCommandModule {
    runCommand: ActionCommand;
}
