// vetur.config.js
/** @type {import('vls').VeturConfig} */
module.exports = {
    // **optional** default: `{}`
    // override vscode settings
    // Notice: It only affects the settings used by Vetur.
    settings: {
        "vetur.useWorkspaceDependencies": true,
        "vetur.validation.interpolation": true,
    },
    // **optional** default: `[{ root: './' }]`
    // support monorepos
    projects: [
        "./docs", // shorthand for only root.
        {
            // **required**
            // Where is your project?
            // It is relative to `vetur.config.js`.
            root: "./client",
            globalComponents: ["./src/components/**/*.vue"],
        },
    ],
};
