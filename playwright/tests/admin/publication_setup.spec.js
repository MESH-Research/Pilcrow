import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../../helpers"

/**
 * @description
 * This test is testing that a publication admin can access the publication
 * configuration page and that they can assign editors and publication admins
 */

test("publication admin setup", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "publicationadministrator@pilcrow.dev")
    await page.goto("/publication/1/setup/basic")
    await expect(page).not.toHaveURL(/error403/)

    //* check accessibility
    let { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    //* configure button is displayed
    await page.goto("/publication/1")
    await expect(page.getByTestId("configure_button")).toBeVisible()

    //* can't access other publications
    await page.goto("/publication/2/setup/basic")
    await expect(page).toHaveURL(/error403/)

    //* Configure button is not displayed for other publications
    await expect(page.getByTestId("configure_button")).toHaveCount(0)

    //* Application admins can assign publication admins
    await page.goto("publication/1/setup/users")

    await page
        .getByTestId("admins_list")
        .getByTestId("input_user")
        .fill("applicationAd")
    await page
        .getByRole("option", { name: "Application Administrator" })
        .click()
    await page.getByTestId("admins_list").getByTestId("button-assign").click()

    await expect(page.getByTestId("admins_list")).toHaveText(
        /Application Administrator/
    )

    //* Application admins can remove publication admins
    await page
        .getByTestId("admins_list")
        .getByRole("listitem")
        .filter({ hasText: "applicationadministrator@pilcrow.dev" })

        .getByTestId("button_unassign")
        .click()

    await expect(page.getByTestId("admins_list")).not.toHaveText(
        /Application Administrator/
    )
    //* Application admins can assign editors
    await page.goto("publication/1/setup/users")

    await page
        .getByTestId("editors_list")
        .getByTestId("input_user")
        .fill("regularUser")
    await page.getByRole("option", { name: "Regular User" }).click()
    await page.getByTestId("editors_list").getByTestId("button-assign").click()

    await expect(page.getByTestId("editors_list")).toHaveText(/Regular User/)

    //* Application admins can remove publication editors
    await page
        .getByTestId("editors_list")
        .getByRole("listitem")
        .filter({ hasText: "regularuser@pilcrow.dev" })

        .getByTestId("button_unassign")
        .click()

    await expect(page.getByTestId("editors_list")).not.toHaveText(
        /Regular User/
    )

    //* Check accessibility on style criteria page
    await page.goto("publication/1/setup/criteria")
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)

    //* should allow editing of style criteria

    await page.getByTestId("editBtn").first().click()

    await page.getByTestId("name-input").fill("Accessibility Update")
    await page
        .getByTestId("description-input")
        .locator(".q-editor__content")
        .fill("Updated description.")
    await page.getByTestId("icon-button").click()

    const newIconName = await page
        .locator(".q-icon-picker__container button")
        .first()
        .locator("i")
        .evaluate((element) => {
            return element.textContent
        }) //get the text content of the icon
    await page.locator(".q-icon-picker__container button").first().click()
    await page.getByTestId("button_save").click()
    await expect(page.getByTestId("listItem").first()).toHaveText(
        /Accessibility Update/
    )
    await expect(page.getByTestId("listItem").first()).toHaveText(
        /Updated description/
    )

    await expect(page.getByTestId("listItem").first()).toContainText(
        newIconName
    )

    //* should allow adding a style criteria

    //Check existing number of items:
    await expect(page.getByTestId("listItem")).toHaveCount(4)

    //Create new item
    await page.getByTestId("add-criteria-button").click()
    await page.getByTestId("name-input").fill("New Criteria")
    await page
        .getByTestId("description-input")
        .locator(".q-editor__content")
        .fill("New criteria description.")
    await page.getByTestId("button_save").click()

    //Check a new item exists
    await expect(page.getByTestId("listItem")).toHaveCount(5)
    await expect(page.getByTestId("listItem").last()).toHaveText(/New Criteria/)
    await expect(page.getByTestId("listItem").last()).toHaveText(
        /New criteria description/
    )
    await expect(page.getByTestId("listItem").last()).toHaveText(/task_alt/)
    //* should allow deleting a style criteria

    await page.getByTestId("editBtn").first().click()
    await page.getByTestId("button-delete").click()

    await page.getByRole("button", { name: "Ok" }).click()

    await expect(page.getByTestId("listItem")).toHaveCount(4)

    //* Check accessibility on basic settings page
    await page.goto("publication/1/setup/basic")
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)

    //* should allow editing basic settings
    await page.getByTestId("name_field").fill("Update")
    await expect(
        page.getByRole("button", { name: "Publicly Visible" })
    ).toHaveAttribute("aria-pressed", "true")
    await page.getByRole("button", { name: "Hidden" }).click()
    await expect(page.getByRole("button", { name: "Hidden" })).toHaveAttribute(
        "aria-pressed",
        "true"
    )
    await expect(
        page.getByRole("button", { name: "Publicly Visible" })
    ).toHaveAttribute("aria-pressed", "false")
    await expect(page.getByRole("button", { name: "Open" })).toHaveAttribute(
        "aria-pressed",
        "true"
    )
    await page.getByRole("button", { name: "Closed" }).click()
    await expect(page.getByRole("button", { name: "Open" })).toHaveAttribute(
        "aria-pressed",
        "false"
    )
    await expect(page.getByRole("button", { name: "Closed" })).toHaveAttribute(
        "aria-pressed",
        "true"
    )

    await page.getByTestId("button_save").click()
    await expect(page.getByTestId("button_save")).toHaveText(/Saved/)

    //* Check accessibility on content page
    await page.goto("publication/1/setup/content")
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)

    //* should allow editing content blocks
    await page.getByTestId("content_block_select").click()

    await page.getByRole("option").filter({ hasText: "Home Page" }).click()
    await page
        .getByTestId("content_field")
        .locator(".q-editor__content")
        .fill("More description.")
    await page.getByTestId("button_save").click()
    await expect(page.getByTestId("button_save")).toHaveText(/Saved/)

    //* creates new publications and navigates to setup page
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("/admin/publications")
    await page.getByTestId("create_pub_button").click()
    violations = (await defaultAxeScan(page).analyze()).violations
    expect(violations).toHaveLength(0)

    await page
        .getByTestId("new_publication_input")
        .fill("Publication from Cypress")
    await page.getByTestId("new_publication_input").press("Enter")
    await expect(page.getByTestId("create_publication_notify")).toHaveClass(
        /bg-positive/
    )
    await expect(page).toHaveURL(/publication\/[0-9]+\/setup\/basic$/)

    //* prevents publication creation when the name is empty
    await page.goto("/admin/publications")
    await page.getByTestId("create_pub_button").click()
    await page.getByTestId("new_publication_input").press("Enter")

    await expect(page.getByTestId("name_field_error")).toBeVisible()

    //* prevents publication creation when the name exceeds the maximum length

    const name_257_characters = "".padEnd(257, "01234567890")
    await page.getByTestId("new_publication_input").fill(name_257_characters)
    await expect(page.getByTestId("name_field_error")).toBeVisible()

    //* prevents publication creation when the name is not unique", () => {

    await page.getByTestId("new_publication_input").fill("Update")
    await page.getByTestId("new_publication_input").press("Enter")
    await expect(page.getByTestId("create_publication_notify")).toHaveClass(
        /bg-negative/
    )

    await expect(page.getByTestId("name_field_error")).toBeVisible()
})
