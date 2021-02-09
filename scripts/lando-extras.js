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
    #extrasConfig = [];
    #localConfig = {};

    /**
     * Setup LandoExtras.
     *
     * @param String ymlPath
     * @param Object options
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
        const extrasFile = this.fullExtrasPath;
        try {
            const extrasYaml = yaml.load(fs.readFileSync(extrasFile));

            Object.entries(extrasYaml).forEach(([name, config]) => {
                const enabled = this.#enabled(config.template);
                this.#extrasConfig.push({ name, enabled, ...config });
            });
        } catch (e) {
            throw `Unable to parse: ${extrasFile}`;
        }
    }

    /**
     * Prefix with file path.
     *
     * @param String file
     */
    #getFullPath(file) {
        return `${this.#ymlPath}${file}`;
    }

    /**
     * Copy configuration template into local config.
     *
     * @param Object template
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
     * @param Object template
     */
    #disableTemplate(template) {
        Object.entries(template).forEach(([tKey, tContent]) => {
            Object.keys(tContent).forEach((key) => {
                delete this.#localConfig[tKey][key];
            });
        });
    }

    /**
     * Return true if a config template is enabled currently.
     *
     * @param Object config
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
     * @param String name
     */
    #get(extra) {
        return this.#extrasConfig.find((e) => e.name == extra);
    }

    /**
     * Enable all extras
     */
    enableAll() {
        this.#extrasConfig.forEach((extra) => {
            this.#enableTemplate(extra.template);
            extra.enabled = true;
        });
    }

    /**
     * Disable All Extras
     */
    disableAll() {
        this.#extrasConfig.forEach((extra) => {
            this.#disableTemplate(extra.template);
            extra.enabled = false;
        });
    }

    /**
     * Enable an extra by name.
     *
     * @param String name
     */
    disable(name) {
        const extra = this.#get(name);
        this.#disableTemplate(extra.template);
        extra.enabled = false;
    }

    /**
     * Enable an extra by name.
     *
     * @param String name
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
     */
    map(callback) {
        return this.#extrasConfig.map(callback);
    }

    /**
     * Return true if the provided extra exists
     *
     * @param String name
     */
    exists(name) {
        return this.#extrasConfig.some((e) => e.name === name);
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
     */
    get fullConfigPath() {
        return this.#getFullPath(this.#localConfigFile);
    }

    /**
     * Get full extras config path
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
 * @param LandoExtras
 * @param Boolean write set to true to pre-confirm overwrite.
 */
async function saveConfig(extras, write) {
    var overwrite = write;
    if (!overwrite) {
        console.log();
        overwrite = await confirmOverwrite();
    }

    if (overwrite) {
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
 */
function unknownExtraError(service) {
    console.error(dedent`
    ${chalk.red("Unknown extra:")} ${chalk.redBright(service)}
    `);
}

/**
 * Return a table of available extras and their enabled status
 *
 * @param LandoExtras config
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
        .command("enable <extra>")
        .alias("e")
        .description("enable an extras service")
        .action(async (service) => {
            if (!extras.exists(service)) {
                unknownExtraError(service);
                return;
            } else {
                console.log(dedent`
                Copying configuration template for ${chalk.whiteBright.bold(
                    service
                )}.
                `);
                extras.enable(service);
                await saveConfig(extras, program.opts().yes);
            }
        });

    program
        .command("disable <extra>")
        .alias("d")
        .description("disable an extras service")
        .action(async (service) => {
            if (!extras.exists(service)) {
                unknownExtraError(service);
            } else {
                console.log(dedent`
                Removing configuration template for ${chalk.whiteBright.bold(
                    service
                )}.
                `);
                extras.disable(service);
                await saveConfig(extras, program.opts().yes);
            }
        });

    await program.parseAsync(process.argv);
}
main();
