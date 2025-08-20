import process from 'node:process';
import { CodegenContext, loadContext, generate } from '@graphql-codegen/cli';
import { normalizePath } from 'vite';

const RESET = "\x1B[0m";
const BRIGHT = "\x1B[1m";
const DIM = "\x1B[2m";
const FG_CYAN = "\x1B[36m";
const LOG_PREFIX = `${FG_CYAN}${BRIGHT}VITE PLUGIN GRAPHQL CODEGEN${RESET} `;
function debugLog(...args) {
  console.log(LOG_PREFIX, DIM, ...args, RESET);
}

async function getDocumentPaths(context) {
  const config = context.getConfig();
  const sourceDocuments = Object.values(config.generates).map(
    (output) => Array.isArray(output) ? void 0 : output.documents
  );
  if (config.documents) {
    sourceDocuments.unshift(config.documents);
  }
  const normalized = sourceDocuments.filter((item) => item !== void 0).flat();
  if (!normalized.length) return [];
  const documents = await context.loadDocuments(normalized);
  if (!documents.length) return [];
  return documents.map(({ location = "" }) => location).filter(Boolean).map(normalizePath);
}
async function getSchemaPaths(context) {
  const config = context.getConfig();
  const sourceSchemas = Object.values(config.generates).map(
    (output) => Array.isArray(output) ? void 0 : output.schema
  );
  if (config.schema) {
    sourceSchemas.unshift(config.schema);
  }
  const normalized = sourceSchemas.filter((item) => !!item).flat();
  if (!normalized.length) return [];
  const schemas = await context.loadSchema(
    // loadSchema supports array of string, but typings are wrong
    normalized
  );
  return schemas.extensions.sources.map(({ name = "" }) => name).filter(Boolean).map(normalizePath);
}
function getGeneratesPaths(context) {
  const config = context.getConfig();
  return Object.keys(config.generates).map(normalizePath);
}

function isCodegenConfig(filePath, context) {
  if (!context.filepath) return false;
  return normalizePath(filePath) === normalizePath(context.filepath);
}
function isGeneratedFile(filePath, context) {
  const generatesPaths = getGeneratesPaths(context);
  const normalizedFilePath = normalizePath(filePath);
  return generatesPaths.some((path) => normalizedFilePath.includes(path));
}

function createMatchCache(context, options) {
  const cache = /* @__PURE__ */ new Set();
  const refresh = async () => {
    const matchers = [];
    if (options.matchOnDocuments) matchers.push(getDocumentPaths(context));
    if (options.matchOnSchemas) matchers.push(getSchemaPaths(context));
    const results = await Promise.all(matchers);
    const entries = results.flat().map(normalizePath);
    cache.clear();
    for (const entry of entries) {
      cache.add(entry);
    }
  };
  return {
    init: refresh,
    refresh,
    has: (filePath) => cache.has(normalizePath(filePath)),
    entries: () => Array.from(cache)
  };
}

const modes = {
  serve: "serve",
  build: "build"
};
const { serve, build } = modes;
function isServeMode(mode) {
  return mode === serve;
}
function isBuildMode(mode) {
  return mode === build;
}

function GraphQLCodegen(options) {
  let codegenContext;
  let viteMode;
  let viteServer;
  const {
    runOnStart = true,
    runOnBuild = true,
    enableWatcher = true,
    watchCodegenConfigFiles = true,
    throwOnStart = false,
    throwOnBuild = true,
    matchOnDocuments = true,
    matchOnSchemas = false,
    project = null,
    config = null,
    configOverride = {},
    configOverrideOnStart = {},
    configOverrideOnBuild = {},
    configOverrideWatcher = {},
    configFilePathOverride,
    debug = false
  } = options ?? {};
  const log = (...args) => {
    if (!debug) return;
    debugLog(...args);
  };
  const generateWithOverride = async (overrideConfig) => {
    const currentConfig = codegenContext.getConfig();
    return await generate({
      ...currentConfig,
      ...configOverride,
      ...overrideConfig,
      // Vite handles file watching
      watch: false
    }).catch((error) => {
      if (viteServer) {
        viteServer.ws.send("error", error);
      }
      throw error;
    });
  };
  if (options) log("Plugin initialized with options:", options);
  return {
    name: "graphql-codegen",
    async config(_userConfig, env) {
      try {
        if (config) {
          log("Manual config passed, creating codegen context");
          codegenContext = new CodegenContext({ config });
        } else {
          const cwd = process.cwd();
          log("Loading codegen context:", configFilePathOverride ?? cwd);
          codegenContext = await loadContext(configFilePathOverride);
        }
        if (project != null) codegenContext.useProject(project);
        log("Loading codegen context successful");
      } catch (error) {
        log("Loading codegen context failed");
        throw error;
      }
      viteMode = env.command;
    },
    async buildStart() {
      if (isServeMode(viteMode)) {
        if (!runOnStart) return;
        try {
          await generateWithOverride(configOverrideOnStart);
          log("Generation successful on start");
        } catch (error) {
          log("Generation failed on start");
          if (throwOnStart) throw error;
        }
      }
      if (isBuildMode(viteMode)) {
        if (!runOnBuild) return;
        try {
          await generateWithOverride(configOverrideOnBuild);
          log("Generation successful on build");
        } catch (error) {
          log("Generation failed on build");
          if (throwOnBuild) throw error;
        }
      }
    },
    configureServer(server) {
      viteServer = server;
      if (!enableWatcher) return;
      const matchCache = createMatchCache(codegenContext, {
        matchOnDocuments,
        matchOnSchemas
      });
      async function checkFile(filePath) {
        log(`Checking file: ${filePath}`);
        if (matchCache.has(filePath)) {
          log("File is in match cache");
          try {
            await generateWithOverride(configOverrideWatcher);
            log("Generation successful in file watcher");
          } catch {
            log("Generation failed in file watcher");
          }
          return;
        }
        if (isCodegenConfig(filePath, codegenContext)) {
          log("Codegen config file matched, restarting vite");
          server.restart();
          return;
        }
        log("File did not match");
      }
      async function initializeWatcher() {
        try {
          log("Match cache initialing");
          await matchCache.init();
          if (watchCodegenConfigFiles) {
            log("Adding codegen config files to watcher", matchCache.entries());
            server.watcher.add(matchCache.entries());
          }
          log("Match cache initialized");
        } catch (error) {
          log("Match cache initialization failed", error);
        }
        server.watcher.on("add", async (filePath) => {
          log(`File added: ${filePath}`);
          if (isGeneratedFile(filePath, codegenContext)) {
            log("File is a generated output file, skipping");
            return;
          }
          try {
            log("Match cache refreshing");
            await matchCache.refresh();
            log("Match cache refreshed");
          } catch (error) {
            log("Match cache refresh failed", error);
          }
          await checkFile(filePath);
        });
        server.watcher.on("change", async (filePath) => {
          log(`File changed: ${filePath}`);
          await checkFile(filePath);
        });
      }
      initializeWatcher();
    }
  };
}

export { GraphQLCodegen, GraphQLCodegen as default };
