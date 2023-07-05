import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("Submission Details", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("/submission/100/details")

    //Check accessibility
    const { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    const reviewerInput = page
        .getByTestId("reviewers_list")
        .getByTestId("input_user")

    const reviewerListItems = page
        .getByTestId("reviewers_list")
        .getByRole("listitem")

    //Check that app admins can assign and remove reviewers
    await reviewerInput.fill("application")
    await page.getByTestId("options_item").click()
    await page.getByTestId("button-assign").click()

    await expect(
        reviewerListItems.filter({ hasText: "Application Administrator" })
    ).toBeVisible()

    await reviewerListItems
        .filter({ hasText: "Application Administrator" })
        .getByRole("button")
        .click()

    await expect(
        reviewerListItems.filter({ hasText: "Application Administrator" })
    ).toHaveCount(0)

    //Check that new users can be invited by email
    await reviewerInput.fill("newuser@pilcrow.dev")
    await page.getByTestId("button-assign").click()

    await expect(
        reviewerListItems.filter({ hasText: "newuser@pilcrow.dev" })
    ).toBeVisible()

    //Check that invalid emails are rejected for invites
    await reviewerInput.fill("newuser@example.com")
    await page.getByTestId("button-assign").click()

    await expect(
        reviewerListItems.filter({ hasText: "newuser@pilcrow.dev" })
    ).toBeVisible()

    //Check that invited users can be re-invited
    await page.getByTestId("user_unconfirmed").click()
    await page.getByTestId("dirtyYesReinviteUser").click()
    await expect(page.getByTestId("reinvite_notify")).toHaveClass(/bg-positive/)

    //Check that review coordinators can assign reviewers
    await login(page, "reviewcoordinator@pilcrow.dev")

    await reviewerInput.fill("application ad")
    await page
        .getByText(
            "applicationAdminUser (applicationadministrator@pilcrow.dev)"
        )
        .click()
    await page.getByTestId("button-assign").click()

    await expect(
        reviewerListItems.filter({ hasText: "Application Administrator" })
    ).toBeVisible()

    await reviewerListItems
        .filter({ hasText: "Application Administrator" })
        .getByRole("button")
        .click()

    await expect(
        reviewerListItems.filter({ hasText: "Application Administrator" })
    ).toHaveCount(0)

    //Should disallow assignments of duplicate reviewers
    await reviewerInput.fill("reviewer@pilcrow")
    await page.getByTestId("options_item").click()
    await page.getByTestId("button-assign").click()

    await expect(
        page.getByTestId("reviewers_list").getByRole("listitem")
    ).toHaveCount(2)

    //Check reviewer invitation form is hidden for reviewers
    await login(page, "reviewer@pilcrow.dev")
    await expect(page.getByTestId("invitation_form")).toHaveCount(0)

    await login(page, "regularuser@pilcrow.dev")
    await page.getByTestId("submission_title").click()
    await page.getByTestId("submission_title_input").fill("Hello World")
    await page.getByTestId("submission_title_input").press("Enter")
    await expect(page.getByTestId("submission_title")).toHaveText("Hello World")

    //Check that status changes appear in activity log
    //TODO: Things have moved and this needs a refactor
    // await login(page, "applicationAdministrator@pilcrow.dev")
    // await page.goto("reviews")

    // await page
    //     .getByRole("listitem")
    //     .filter({ hasText: "Hello World" })
    //     .getByTestId("submission_actions")
    //     .click()

    // await page.getByTestId("change_status").click()
    // await page.getByTestId("close_review").click()
    // await page
    //     .getByTestId("status_change_comment")
    //     .fill("first comment from admin")

    // await page.getByTestId("dirtyYesChangeStatus").click()

    // await expect(page.getByTestId("change_status_notify")).toHaveClass(
    //     /bg-positive/
    // )
    // await page.getByTestId("accept_as_final").click()
    // await page
    //     .getByTestId("status_change_comment")
    //     .fill("second comment from admin")
    // await page.getByTestId("dirtyYesChangeStatus").click()

    // await expect(page.getByTestId("change_status_notify")).toHaveClass(
    //     /bg-positive/
    // )

    // await page.goto("submission/100/details")
    // await expect(page.getByTestId("activity_section")).toContainText(
    //     "first comment from admin"
    // )
    // await expect(page.getByTestId("activity_section")).toContainText(
    //     "second comment from admin"
    // )
})
