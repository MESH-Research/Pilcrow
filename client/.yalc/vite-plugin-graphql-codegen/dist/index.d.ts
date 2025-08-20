import { CodegenConfig } from '@graphql-codegen/cli';
import { Plugin } from 'vite';

interface Options {
    /**
     * Run codegen on server start.
     *
     * @default true
     */
    runOnStart?: boolean;
    /**
     * Run codegen on build. Will prevent build if codegen fails.
     *
     * @default true
     */
    runOnBuild?: boolean;
    /**
     * Enable codegen integration with vite file watcher.
     *
     * @default true
     */
    enableWatcher?: boolean;
    /**
     * Automatically add schemas and documents referenced in the codegen config
     * to the Vite file watcher.
     *
     * @default true
     */
    watchCodegenConfigFiles?: boolean;
    /**
     * Throw an error if codegen fails on server start.
     *
     * @default false
     */
    throwOnStart?: boolean;
    /**
     * Throw an error if codegen fails on build.
     *
     * @default true
     */
    throwOnBuild?: boolean;
    /**
     * Run codegen when a document matches.
     *
     * @default true
     */
    matchOnDocuments?: boolean;
    /**
     * Run codegen when a schema matches.
     *
     * @default false
     */
    matchOnSchemas?: boolean;
    /**
     * Name of a project in a multi-project config file.
     */
    project?: string;
    /**
     * Manually define the codegen config.
     */
    config?: CodegenConfig;
    /**
     * Override parts of the codegen config just for this plugin.
     */
    configOverride?: Partial<CodegenConfig>;
    /**
     * Override parts of the codegen config just for this plugin on server start.
     */
    configOverrideOnStart?: Partial<CodegenConfig>;
    /**
     * Override parts of the codegen config just for this plugin on build.
     */
    configOverrideOnBuild?: Partial<CodegenConfig>;
    /**
     * Override parts of the codegen config just for this plugin in the watcher.
     */
    configOverrideWatcher?: Partial<CodegenConfig>;
    /**
     * Override the codegen config file path.
     */
    configFilePathOverride?: string;
    /**
     * Log various steps to aid in tracking down bugs.
     *
     * @default false
     */
    debug?: boolean;
}
declare function GraphQLCodegen(options?: Options): Plugin;

export { GraphQLCodegen, GraphQLCodegen as default };
export type { Options };
