import { expect, test } from "@playwright/test"
import { defaultAxeScan, resetDb } from "../helpers"

test("home page", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await page.goto("/")

    await expect(page).toHaveTitle(/Pilcrow/)

    const { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    await page.getByRole("link", { name: "Login" }).click()
    await expect(page).toHaveURL(/login$/)

    await page.goto("/")
    await page.getByRole("link", { name: "Register" }).click()

    await expect(page).toHaveURL(/register$/)
})
