import { expect, test } from "@playwright/test"
import { defaultAxeScan, resetDb } from "../helpers"

test.use({ storageState: ".auth/regularUser.json" })

test("account page", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await page.goto("/account/profile")

    await page.getByTestId("update_user_username").fill("Updated User")
    await page.getByRole("textbox", { name: "Username" }).fill("Updated User")
    await page.getByRole("button", { name: "Save" }).click()
    await expect(page.getByTestId("update_user_notify")).toHaveClass(
        /bg-positive/
    )
    await page.getByTestId("button_dismiss_notify").click()
    await expect(page.getByTestId("avatar_username")).toContainText(
        "@Updated User"
    )

    await page.getByTestId("update_user_password").fill("newPassword!@#")
    await page.getByTestId("button_save").click()
    await expect(page.getByTestId("update_user_notify")).toHaveClass(
        /bg-positive/
    )
    await page.getByTestId("button_dismiss_notify").click()

    await page.getByTestId("update_user_name").fill("New Name")
    await page.getByTestId("button_save").click()
    await expect(page.getByTestId("update_user_notify")).toHaveClass(
        /bg-positive/
    )
    await page.getByTestId("button_dismiss_notify").click()
    await expect(page.getByTestId("avatar_name")).toContainText("New Name")
})

test("should assert the page is accessible", async ({ page }) => {
    await page.goto("/account/profile")

    const { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)
})
