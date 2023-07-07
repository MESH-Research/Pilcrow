import AxeBuilder from "@axe-core/playwright"

export function defaultAxeScan(page) {
    return new AxeBuilder({ page }).withTags([
        "wcag2a",
        "wcag2aa",
        "wcag21a",
        "wcag21aa",
    ])
}
