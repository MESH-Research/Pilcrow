/// <reference types="vitest" />
import { builtinModules } from "node:module";
import { resolve } from "node:path";
import { defineConfig } from "vite";

const external = [...builtinModules, ...builtinModules.map((m) => `node:${m}`)];

export default defineConfig({
    resolve: {
        alias: {
            "/src": resolve(__dirname, "src"),
            "/commands": resolve(__dirname, "src/commands"),
            "/lib": resolve(__dirname, "src/lib"),
        },
    },
    build: {
        sourcemap: true,
        rollupOptions: {
            input: {
                main: resolve(__dirname, "src/main.ts"),
                post: resolve(__dirname, "src/post.ts"),
                // pre: resolve(__dirname, "src/pre.ts"),
            },
            external,
            output: [
                {
                    format: "es",
                    entryFileNames: "[name].js",
                },
            ],
            preserveSymlinks: true,
        },
    },
});
