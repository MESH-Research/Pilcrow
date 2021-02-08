const { program } = require("commander");
const yaml = require("yaml-js");
const fs = require("fs");
const chalk = require("chalk");
const columnify = require("columnify");
const yesno = require("yesno");
const dedent = require("dedent-js");

class LandoExtras {
    ymlPath;
    localConfigFile;
    extrasConfigFile;
    extras = {};
    localConfig = {};

    /**
     * Setup LandoExtras.
     *
     * @param String ymlPath
     * @param Object options
     */
    constructor(ymlPath, { localConfigFile, extrasConfigFile } = {}) {
        this.ymlPath = ymlPath ?? "./";
        this.localConfigFile = localConfigFile ?? ".lando.local.yml";
        this.extrasConfigFile = extrasConfigFile ?? ".lando.extras.yml";
        this.parseLocalYml();
        this.parseExtrasYml();
    }

    /**
     * Enable an extra by name.
     *
     * @param String extra
     */
    enableExtra(extra) {
        Object.keys(this.extras[extra].template).forEach((tKey) => {
            if (!this.localConfig[tKey]) {
                this.localConfig[tKey] = {};
            }
            Object.assign(
                this.localConfig[tKey],
                this.extras[extra].template[tKey]
            );
        });
        this.extras[extra].enabled = true;
    }

    /**
     * Enable an extra by name.
     *
     * @param String extra
     */
    disableExtra(extra) {
        Object.entries(this.extras[extra].template).forEach(
            ([tKey, tContent]) => {
                Object.keys(tContent).forEach((key) => {
                    delete this.localConfig[tKey][key];
                });
            }
        );
        this.extras[extra].enabled = false;
    }

    /**
     * Return an extra by name
     *
     * @param String extra
     */
    getExtra(extra) {
        return this.extras[extra];
    }

    /**
     * Parse local config yaml file.
     */
    parseLocalYml() {
        try {
            this.localConfig = yaml.load(
                fs.readFileSync(this.getFullPath(this.localConfigFile))
            );
        } catch (e) {
            this.localConfig = {};
        }
    }

    /**
     * Save local config yaml file.
     */
    writeLocalYml() {
        try {
            fs.writeFileSync(
                this.getFullPath(this.localConfigFile),
                yaml.dump(this.localConfig)
            );
        } catch (e) {
            throw `Unable to write: ${this.getFullPath(this.localConfigFile)}`;
        }
    }

    /**
     * Read extras config file.
     */
    parseExtrasYml() {
        try {
            this.extras = yaml.load(
                fs.readFileSync(this.getFullPath(this.extrasConfigFile))
            );
            Object.entries(this.extras).forEach(([name, config]) => {
                config.enabled = this.isExtraEnabled(name);
            });
        } catch (e) {
            throw `Unable to parse: ${this.getFullPath(this.extrasConfigFile)}`;
        }
    }

    /**
     * Prefix with file path.
     *
     * @param String file
     */
    getFullPath(file) {
        return `${this.ymlPath}${file}`;
    }

    /**
     * Return true if an extra is enabled currently.
     *
     * @param String extra
     */
    isExtraEnabled(extra) {
        const NotEnabledException = {};
        try {
            Object.entries(this.extras[extra].template).forEach(
                ([tKey, tKeyContent]) => {
                    Object.keys(tKeyContent).forEach((item) => {
                        if (!this.localConfig?.[tKey]?.[item]) {
                            throw NotEnabledException;
                        }
                    });
                }
            );
        } catch (e) {
            if (e === NotEnabledException) {
                return false;
            }
            throw e;
        }
        return true;
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
async function saveConfig(config, write) {
    var overwrite = write;
    if (!overwrite) {
        console.log();
        overwrite = await confirmOverwrite();
    }

    if (overwrite) {
        config.writeLocalYml();
        console.log(dedent`

        ${chalk.green(`Updated: ${config.getFullPath(config.localConfigFile)}`)}

        ${getActionsList(config)}

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
function getActionsList(config) {
    var list = [];
    Object.entries(config.extras).forEach(
        ([extra, { description, enabled }]) => {
            list.push({
                extra,
                description,
                icon: enabled ? `[${chalk.green("✔")}]` : "[ ]",
                status: enabled ? chalk.green("enabled") : "disabled",
            });
        }
    );
    return columnify(list, {
        minWidth: 15,
        config: {
            icon: { showHeaders: false, minWidth: 2 },
        },
        columns: ["icon", "extra", "description", "status"],
    });
}

/** CLI Logic */
async function main() {
    const config = new LandoExtras("./");
    console.log(dedent`
    Manage 'extras' templates for local lando config.
    
    Loaded templates from: ${chalk.yellow(
        config.getFullPath(config.extrasConfigFile)
    )}`);
    program
        .name("lando extras")
        .addHelpCommand(false)
        .option("-y --yes", "Answer yes to overwrite prompts");

    program
        .command("list", { isDefault: true, hidden: true })
        .description("list available extras templates")
        .action(() => {
            console.log(dedent`
            
            ${getActionsList(config)}
            
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
            const enable = onoff == "on" ? true : false;

            Object.keys(config.extras).forEach((extra) => {
                if (enable) {
                    config.enableExtra(extra);
                } else {
                    config.disableExtra(extra);
                }
            });

            await saveConfig(config, program.opts().yes);
        });

    program
        .command("enable <extra>")
        .alias("e")
        .description("enable an extras service")
        .action(async (service) => {
            if (!config.getExtra(service)) {
                unknownExtraError(service);

                return;
            } else {
                console.log(dedent`
                Copying configuration template for ${chalk.whiteBright.bold(
                    service
                )}.
                `);
                config.enableExtra(service);
                await saveConfig(config, program.opts().yes);
            }
        });

    program
        .command("disable <extra>")
        .alias("d")
        .description("disable an extras service")
        .action(async (service) => {
            if (!config.getExtra(service)) {
                unknownExtraError(service);
            } else {
                console.log(dedent`
                Removing configuration template for ${chalk.whiteBright.bold(
                    service
                )}.
                `);
                config.disableExtra(service);
                await saveConfig(config, program.opts().yes);
            }
        });

    await program.parseAsync(process.argv);
}
main();
