import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../../helpers"

test("Admin Users Index", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")

    //* restricts access based on role"
    await page.goto("/admin/users")
    await expect(page).toHaveURL(/error403/)

    //* allows access based on role
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("/admin/users")
    await expect(page).not.toHaveURL(/error403/)

    //* should assert the page is accessible
    // Inject the axe-core libraray
    let { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    //* should assert the User Details page of an admin is accessible
    await page.goto("/admin/user/2")
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)

    //* should assert the User Details page of an non-admin is accessible
    await page.goto("/admin/user/1")
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)
})
