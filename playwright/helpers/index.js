export * from "./api"
export * from "./axe"
export * from "./database"

export function getContaining(locator, element) {
    return locator.filter({ has: element })
}

export function getInputLabel(input) {
    return getContaining(input.page().locator("label"), input)
}
