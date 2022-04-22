import yaml from 'yaml-js'
import fs from 'fs'

export default class LandoExtras {
    #ymlPath;
    #localConfigFile;
    #extrasConfigFile;
    #extras = [];
    #localConfig = {};

    /**
     * Set Up LandoExtras.
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
     * @param {Object} template Configuration template to check.
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
     * Disable an extra by name.
     *
     * @param {string} name Name of extra to disable
     */
    disable(name) {
        const extra = this.#get(name);
        this.#disableTemplate(extra.template);
        extra.enabled = false;
    }

    /**
     * Enable an extra by name.
     *
     * @param {string} name Name of extra to enable
     */
    enable(name) {
        const extra = this.#get(name);
        this.#enableTemplate(extra.template);
        extra.enabled = true;
    }

    /**
     * Map extra configs.
     *
     * @param {Function} callback
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
