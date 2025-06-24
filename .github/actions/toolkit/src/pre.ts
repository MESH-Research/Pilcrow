import { getInput } from "@actions/core";
import { run } from "./action";
const inputs = {
    "docker-metadata": getInput("docker-metadata"),
    "bake-files": getInput("bake-files"),
    target: getInput("target"),
    command: getInput("command", { required: true }),
};
run("pre", inputs);
