import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("account profile page", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")
    await page.goto("/account/profile")

    const saveButton = page.getByTestId("button_save")

    const { violations } = await defaultAxeScan(page).analyze()
    expect.soft(violations).toHaveLength(0)

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
    await saveButton.click()
    await expect(page.getByTestId("update_user_notify")).toHaveClass(
        /bg-positive/
    )
    await page.getByTestId("button_dismiss_notify").click()

    await page.getByTestId("update_user_name").fill("New Name")
    await saveButton.click()
    await expect(page.getByTestId("update_user_notify")).toHaveClass(
        /bg-positive/
    )
    await page.getByTestId("button_dismiss_notify").click()
    await expect(page.getByTestId("avatar_name")).toContainText("New Name")
})

test("account metadata page", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")
    await page.goto("/account/metadata")

    const saveButton = page.getByTestId("button_save")

    const { violations } = await defaultAxeScan(page).analyze()
    expect.soft(violations).toHaveLength(0)

    await page
        .getByTestId("professional_title")
        .fill("Updated Professsional Title")
    await saveButton.click()
    await expect(saveButton).toContainText("Saved")

    await page.getByTestId("facebook").fill("facebookusername")
    await expect(saveButton).not.toContainText("Saved")
    const responsePromise = page.waitForResponse("**/graphql")
    await saveButton.click()
    await responsePromise
    await expect(saveButton).toContainText("Saved")
    const websitesListControl = page.getByTestId("websites_list_control")

    //Add a website by clicking the add button
    await websitesListControl
        .getByTestId("input_field")
        .fill("https://pilcrow.dev")
    await websitesListControl.getByRole("button", { name: "Add" }).click()

    // Add a website by pressing enter
    await websitesListControl
        .getByTestId("input_field")
        .fill("https://yahoo.com")
    await websitesListControl.getByTestId("input_field").press("Enter")

    //Edit a website
    await websitesListControl.getByText("https://pilcrow.dev").click()
    await page.getByTestId("edit_input_0").fill("https://msu.edu")
    await websitesListControl.getByRole("button", { name: "Save" }).click()

    //Get the websites list
    const items = websitesListControl.getByRole("listitem")
    expect(await items.count()).toBe(2)

    await expect(items.first()).toContainText("https://msu.edu")
    await expect(items.last()).toContainText("https://yahoo.com")

    //Reorder the bottom element to the top by drag and drop
    await items.last().getByText("reorder").dragTo(items.first())
    await expect(items.last()).toContainText("https://msu.edu")
    await expect(items.first()).toContainText("https://yahoo.com")

    //Reorder the top element to the bottom by clicking reorder buttons
    await page
        .getByTestId("collapse_toolbar_0")
        .getByRole("button", { name: "Move Website Down" })
        .click()
    await expect(items.first()).toContainText("https://msu.edu")
    await expect(items.last()).toContainText("https://yahoo.com")

    //Delete the top website
    await page
        .getByTestId("collapse_toolbar_0")
        .getByRole("button", { name: "Delete Website" })
        .click()
    expect(await items.count()).toBe(1)

    //Add insterest keywords
    await page
        .getByTestId("interest_keywords_control")
        .getByTestId("input_field")
        .fill("manuscript")
    await page
        .getByTestId("interest_keywords_control")
        .getByTestId("input_field")
        .press("Enter")

    await page
        .getByTestId("interest_keywords_control")
        .getByTestId("input_field")
        .fill("video")
    await page
        .getByTestId("interest_keywords_control")
        .getByRole("button", { name: "Add" })
        .click()

    //Check that the keywords were added
    await expect(
        await page.getByTestId("tag_list").locator(".q-chip").count()
    ).toBe(2)

    //Delete the first keyword
    await page
        .getByTestId("tag_list")
        .locator(".q-chip")
        .filter({ hasText: "manuscript" })
        .getByRole("button", { name: "Remove" })
        .click()

    await expect(
        await page.getByTestId("tag_list").locator(".q-chip").count()
    ).toBe(1)

    await page.getByTestId("professional_title").fill("Test")
    await page.getByRole("menuitem", { name: "Account Information" }).click()
    await page.getByTestId("dirtyKeepChanges").click()

    await expect(page).toHaveURL(/account\/metadata$/)
    await page.getByTestId("dropdown_username").click()
    await page.getByRole("listitem").filter({ hasText: "Logout" }).click()
    await page.getByTestId("dirtyDiscardChanges").click()
    await expect(page).toHaveURL("/")
})
