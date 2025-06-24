import * as core from "@actions/core";

type ConfigValues = {
    token: string | undefined;
    "oras-bundle-type": string;
    "oras-actor": string | undefined;
    "output-cache-path": string;
};

type ConfigKey = keyof ConfigValues;

type ConfigValuesFunctions = {
    [K in keyof ConfigValues]: () => ConfigValues[K] | ConfigValues[K];
};

export function getConfigValue<T extends ConfigKey>(key: T): ConfigValues[T] {
    const values: ConfigValuesFunctions = {
        token: () => core.getInput("token") ?? process.env.GITHUB_TOKEN,
        "oras-bundle-type": () =>
            core.getInput("oras-bundle-type") ?? "unknown/unknown",
        "oras-actor": () =>
            core.getInput("oras-actor") ?? process.env.GITHUB_ACTOR,
        "output-cache-path": () => core.getInput("output-cache-path"),
    };

    return values[key]();
}
