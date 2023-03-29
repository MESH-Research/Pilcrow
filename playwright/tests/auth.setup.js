import { expect, test as setup } from "@playwright/test"

setup("authenticate applicationAdmin", async ({ page }) => {
    await saveContext(
        page,
        {
            username: "applicationAdminUser",
            email: "applicationadministrator@pilcrow.dev",
            password: "adminPassword!@#",
        },
        ".auth/applicationAdmin.json"
    )
})

setup("authenticate publicationAdministrator", async ({ page }) => {
    await saveContext(
        page,
        {
            username: "publicationAdministrator",
            email: "publicationAdministrator@pilcrow.dev",
            password: "publicationadminPassword!@#",
        },
        ".auth/publicationAdmin.json"
    )
})

setup("authenticate regularUser", async ({ page }) => {
    await saveContext(
        page,
        {
            username: "regularUser",
            email: "regularuser@pilcrow.dev",
            password: "regularPassword!@#",
        },
        ".auth/regularUser.json"
    )
})

async function saveContext(page, user, path) {
    await page.goto("/login")
    await page.getByLabel("Email").fill(user.email)
    await page.getByRole("textbox", { name: "Password" }).fill(user.password)
    await page.getByRole("button", { name: "Login" }).click()
    await expect(
        page.getByRole("button", { name: "User Account Dropdown Toggle" })
    ).toContainText(user.username)

    await page.context().storageState({ path })
}
