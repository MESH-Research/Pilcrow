const { program } = require("commander");
const yaml = require("yaml-js");
const fs = require("fs");
const chalk = require("chalk");
const columnify = require("columnify");
const yesno = require("yesno");
const dedent = require("dedent-js");

class LandoExtras {
    #ymlPath;
    #localConfigFile;
    #extrasConfigFile;
    #extras = [];
    #localConfig = {};

    /**
     * Setup LandoExtras.
     *
     * @param {string} [ymlPath] Path for yaml config files.
     * @param {Object} [options] Options object
     * @param {string} [options.localConfigFile] Name of local config file
     * @param {string} [options.extraConfigFile] Name of the extras config file
     */
    constructor(ymlPath, { localConfigFile, extrasConfigFile } = {}) {
        this.#ymlPath = ymlPath ?? "./";
        this.#localConfigFile = localConfigFile ?? ".lando.local.yml";
        this.#extrasConfigFile = extrasConfigFile ?? ".lando.extras.yml";
        this.#parseLocalYml();
        this.#parseExtrasYml();
    }

    /**
     * Parse local config yaml file.
     */
    #parseLocalYml() {
        try {
            this.#localConfig = yaml.load(fs.readFileSync(this.fullConfigPath));
        } catch (e) {
            this.#localConfig = {};
        }
    }

    /**
     * Read extras config file.
     */
    #parseExtrasYml() {
        try {
            const extrasYaml = yaml.load(fs.readFileSync(this.fullExtrasPath));

            Object.entries(extrasYaml).forEach(([name, config]) => {
                const enabled = this.#enabled(config.template);
                this.#extras.push({ name, enabled, ...config });
            });
        } catch (e) {
            throw `Unable to parse: ${this.fullExtrasPath}`;
        }
    }

    /**
     * Prefix with file path.
     *
     * @param {string} file File name to prefix with yaml path.
     * @returns {string} Prefixed path.
     */
    #getFullPath(file) {
        return `${this.#ymlPath}${file}`;
    }

    /**
     * Copy configuration template into local config.
     *
     * @param {Object} template Config template to enable
     */
    #enableTemplate(template) {
        Object.keys(template).forEach((tKey) => {
            if (!this.#localConfig[tKey]) {
                this.#localConfig[tKey] = {};
            }
            Object.assign(this.#localConfig[tKey], template[tKey]);
        });
    }

    /**
     * Remove template configration keys from localConfig
     *
     * @param {Object} template Config template to remove
     */
    #disableTemplate(template) {
        Object.entries(template).forEach(([tKey, tContent]) => {
            Object.keys(tContent).forEach((key) => {
                delete this.#localConfig[tKey][key];
            });
        });
    }

    /**
     * Return true if a config template is enabled in localConfig
     *
     * @param {Object} config Configuration template to check.
     * @returns {boolean}
     */
    #enabled(template) {
        const NotEnabledException = {};
        try {
            Object.entries(template).forEach(([tKey, tKeyContent]) => {
                Object.keys(tKeyContent).forEach((item) => {
                    if (!this.#localConfig?.[tKey]?.[item]) {
                        throw NotEnabledException;
                    }
                });
            });
        } catch (e) {
            if (e === NotEnabledException) {
                return false;
            }
            throw e;
        }
        return true;
    }

    /**
     * Return an extra by name
     *
     * @param {string} name Name of extra to locate
     * @returns {Object}
     */
    #get(extra) {
        return this.#extras.find((e) => e.name == extra);
    }

    /**
     * Enable all extras
     */
    enableAll() {
        this.#extras.forEach((extra) => {
            this.#enableTemplate(extra.template);
            extra.enabled = true;
        });
    }

    /**
     * Disable All Extras
     */
    disableAll() {
        this.#extras.forEach((extra) => {
            this.#disableTemplate(extra.template);
            extra.enabled = false;
        });
    }

    /**
     * Enable an extra by name.
     *
     * @param {string} name Name of extra to enable
     */
    disable(name) {
        const extra = this.#get(name);
        this.#disableTemplate(extra.template);
        extra.enabled = false;
    }

    /**
     * Enable an extra by name.
     *
     * @param {string} name Name of extra to disable
     */
    enable(name) {
        const extra = this.#get(name);
        this.#enableTemplate(extra.template);
        extra.enabled = true;
    }

    /**
     * Map extra configs.
     *
     * @param Callable callback
     * @returns {any} Results of map call.
     */
    map(callback) {
        return this.#extras.map(callback);
    }

    /**
     * Return true if the provided extra exists
     *
     * @param {string} name
     * @returns {boolean}
     */
    exists(name) {
        return this.#extras.some((e) => e.name === name);
    }

    /**
     * Save local config yaml file.
     */
    write() {
        const fullPath = this.fullConfigPath;
        try {
            fs.writeFileSync(fullPath, yaml.dump(this.#localConfig));
        } catch (e) {
            throw `Unable to write: ${fullPath}`;
        }
    }

    /**
     * Get full local config path
     *
     * @returns {string}
     */
    get fullConfigPath() {
        return this.#getFullPath(this.#localConfigFile);
    }

    /**
     * Get full extras config path
     *
     * @returns {string}
     */
    get fullExtrasPath() {
        return this.#getFullPath(this.#extrasConfigFile);
    }
}

/**
 * Display overwrite confirmation prompt.
 *
 * @returns Boolean true on confirmation
 */
async function confirmOverwrite() {
    return await yesno({
        question: `${chalk.yellow(
            "❔"
        )} Write config to .lando.local.yml? ${chalk.gray("(y/N)")}`,
        defaultValue: false,
    });
}

/**
 * Save configuration, checking for confirmation if needed.
 *
 * @param {LandoExtras}
 * @param {boolean} overwrite set to true to pre-confirm overwrite.
 */
async function saveConfig(extras, overwrite) {
    var write = overwrite;
    if (!write) {
        console.log();
        write = await confirmOverwrite();
    }

    if (write) {
        extras.write();
        console.log(dedent`

        ${chalk.green(`Updated: ${extras.fullConfigPath}`)}

        ${getActionsList(extras)}

        ${chalk.yellow("Don't forget to run lando rebuild!")}
        `);
    } else {
        console.log("\n", chalk.yellow("Nothing written"));
    }
}

/**
 * Display an error message for an unknown service
 *
 * @param {string} name Name of extra that was not found.
 */
function unknownExtraError(name) {
    console.error(dedent`
        ${chalk.red("Unknown extra:")} ${chalk.redBright(name)}
    `);
}

/**
 * Return a table of available extras and their enabled status
 *
 * @param {LandoExtras} extras
 */
function getActionsList(extras) {
    const list = extras.map((e) => {
        const { name, description, enabled } = e;
        return {
            name,
            description,
            icon: enabled ? `[${chalk.green("✔")}]` : "[ ]",
            status: enabled ? chalk.green("enabled") : "disabled",
        };
    });

    return columnify(list, {
        minWidth: 15,
        config: {
            icon: { showHeaders: false, minWidth: 2 },
        },
        columns: ["icon", "name", "description", "status"],
    });
}

/** CLI Logic */
async function main() {
    const extras = new LandoExtras("./");
    console.log(dedent`
        Manage 'extras' templates for local lando config.
        
        Loaded templates from: ${chalk.yellow(extras.fullExtrasPath)}
    `);
    program
        .name("lando extras")
        .addHelpCommand(false)
        .option("-y --yes", "Answer yes to overwrite prompts");

    program
        .command("list", { isDefault: true, hidden: true })
        .description("list available extras templates")
        .action(() => {
            console.log(dedent`
            
            ${getActionsList(extras)}
            
            `);
            program.help();
        });

    program
        .command("all <onoff>")
        .description("add or remove all extras' configuration")
        .alias("a")
        .action(async (onoff) => {
            if (!["on", "off"].includes(onoff)) {
                console.log(chalk.red("Invalid option.  all <on|off>"));
                return;
            }

            if (onoff == "on") {
                extras.enableAll();
            } else {
                extras.disableAll();
            }

            await saveConfig(extras, program.opts().yes);
        });

    program
        .command("enable <name>")
        .alias("e")
        .description("enable an extras service")
        .action(async (name) => {
            if (!extras.exists(name)) {
                unknownExtraError(name);
                return;
            } else {
                console.log(dedent`
                    Copying configuration template for ${chalk.whiteBright.bold(
                        name
                    )}.
                `);
                extras.enable(name);
                await saveConfig(extras, program.opts().yes);
            }
        });

    program
        .command("disable <name>")
        .alias("d")
        .description("disable an extras service")
        .action(async (name) => {
            if (!extras.exists(name)) {
                unknownExtraError(name);
            } else {
                console.log(dedent`
                    Removing configuration template for ${chalk.whiteBright.bold(
                        name
                    )}.
                `);
                extras.disable(name);
                await saveConfig(extras, program.opts().yes);
            }
        });

    await program.parseAsync(process.argv);
}
main();
