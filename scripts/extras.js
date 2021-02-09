const { program } = require("commander");
const chalk = require("chalk");
const columnify = require("columnify");
const yesno = require("yesno");
const dedent = require("dedent-js");
const LandoExtras = require("./lib/lando-extras");

const error = chalk.red;
const errorBold = chalk.redBright.bold;
const info = chalk.yellow;
const aside = chalk.gray;
const success = chalk.green;

/**
 * Display overwrite confirmation prompt.
 *
 * @returns {boolean} true on confirmation
 */
async function confirmOverwrite() {
    return await yesno({
        question: `${info("❔")} Write config to .lando.local.yml? ${aside(
            "(y/N)"
        )}`,
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

        ${success(`Updated: ${extras.fullConfigPath}`)}

        ${getActionsList(extras)}

        ${info("Don't forget to run lando rebuild!")}
        `);
    } else {
        console.log("\n", info("Nothing written"));
    }
}

/**
 * Display an error message for an unknown service
 *
 * @param {string} name Name of extra that was not found.
 */
function unknownExtraError(name) {
    console.error(dedent`
        ${error("Unknown extra:")} ${errorBold(name)}
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
            icon: enabled ? `[${success("✔")}]` : "[ ]",
            status: enabled ? success("enabled") : aside("disabled"),
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
        ${info(`Loaded templates from: ${extras.fullExtrasPath}`)}
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
                console.log(error("Invalid option.  all <on|off>"));
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
                console.log(
                    info(`Copying configuration template for ${name}.`)
                );
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
                console.log(
                    info(`Removing configuration template for ${name}.`)
                );
                extras.disable(name);
                await saveConfig(extras, program.opts().yes);
            }
        });

    await program.parseAsync(process.argv);
}
main();
