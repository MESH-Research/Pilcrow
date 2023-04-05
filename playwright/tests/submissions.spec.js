import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("submission creation", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("submissions")

    const { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    await page
        .getByTestId("new_submission_title_input")
        .fill("Submission from Cypress")
    await page.getByTestId("new_submission_publication_input").click()
    await page
        .getByRole("option", { name: "Pilcrow Test Publication 1" })
        .click()
    await page
        .getByTestId("new_submission_file_upload_input")
        .setInputFiles("fixtures/test.txt")
    await page.getByTestId("save_submission").click()
    await expect(page.getByTestId("submissions_list")).toHaveText(
        /Submission from Cypress/
    )
    await expect(page.getByTestId("create_submission_notify")).toHaveClass(
        /bg-positive/
    )

    await expect(page.locator(".q-notification--top-enter-active")).toHaveCount(
        0
    )

    await page
        .getByTestId("submission_link")
        .filter({ hasText: "Submission from Cypress" })
        .click()
    await expect(page.getByTestId("submitters_list")).toHaveText(
        /applicationadministrator@pilcrow.dev/
    )
    //* prevents submission creation when the title exceeds the maximum length"

    await page.goto("submissions")
    await page
        .getByTestId("new_submission_title_input")
        .fill("1234567890".repeat(52))
    await page.getByTestId("new_submission_title_input").press("Enter")
    await expect(page.getByTestId("create_submission_notify")).toHaveClass(
        /bg-negative/
    )
    await expect(page.locator(".q-notification--top-enter-active")).toHaveCount(
        0
    )
})

test("submission management", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("submission/review/101")

    await expect(page.getByTestId("submission_status")).toHaveText(
        /Initially Submitted/
    )
    await page.getByTestId("accept_for_review").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Awaiting Review/
    )

    await login(page, "reviewer@pilcrow.dev")
    await expect(page).not.toHaveURL("/error403")

    await login(page, "applicationadministrator@pilcrow.dev")

    //* Administrator can open and close a review.
    await page.goto("submission/review/108")
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Awaiting Review/
    )
    await page.getByTestId("open_for_review").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Under Review/
    )
    await expect(page.getByTestId("decision_options")).toBeVisible()
    await page.getByTestId("close_for_review").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Awaiting Decision/
    )
    await expect(page.getByTestId("decision_options")).toBeVisible()

    //* Rejected submissions should not display decision options
    await page.goto("submission/review/102")
    await expect(page.getByTestId("submission_status")).toHaveText(/Rejected/)
    await expect(page.getByTestId("decision_options")).toHaveCount(0)

    //* Should not display the decision options for submissions requested for resubmission"
    await page.goto("submission/review/103")
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Resubmission Requested/
    )
    await expect(page.getByTestId("decision_options")).toHaveCount(0)

    //* Should deny an application administrator from changing status of rejected submissions
    await page.goto("submissions")
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 3" })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("change_status")).toHaveClass(/disabled/)
    await page.getByTestId("change_status_item_section").hover()
    await expect(
        page.getByTestId("cannot_change_submission_status_tooltip")
    ).toBeVisible()

    //* Should allow an application administrator to access rejected submissions
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 3" })
        .getByTestId("submission_actions")
        .click()
    await page.getByTestId("review").click()
    await expect(page).toHaveURL(/submission\/review/)

    //* should allow an application administrator to access submissions requested for resubmission
    await page.goto("submissions")
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 4" })
        .getByTestId("submission_actions")
        .click()
    await page.getByTestId("review").click()
    await expect(page).toHaveURL(/submission\/review/)

    //* should remove the submission in draft status for users who are not associated with the submission
    await page.goto("submissions")
    await expect(
        page
            .getByRole("listitem")
            .filter({ hasText: "Pilcrow Test Submission 5" })
    ).toHaveCount(0)

    //* should allow an application administrator to open and close a review from submissions list

    await page.goto("submissions")
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 2" })
        .getByTestId("submission_actions")
        .click()

    await page.getByTestId("change_status").click()
    await page.getByTestId("open_review").click()

    await page.getByTestId("dirtyYesChangeStatus").click()

    await expect(page.getByTestId("change_status_notify")).toHaveClass(
        /bg-positive/
    )
    await expect(page.getByTestId("change_status_notify")).toHaveCount(0, {
        timeout: 10000,
    })
    await page.getByTestId("all_submissions_title").click()
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 2" })
        .getByTestId("submission_actions")
        .click()
    await page.getByTestId("change_status").click()
    await page.getByTestId("accept_as_final")
    await page.getByTestId("close_review").click()

    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("change_status_notify")).toHaveClass(
        /bg-positive/
    )
})
test("submission reviewer access", async ({ page, baseURL }) => {
    //* should deny a reviewer from changing the status of rejected submissions

    await resetDb(baseURL)
    await login(page, "reviewer@pilcrow.dev")
    await page.goto("submissions")
    await page
        .getByRole("listitem")
        .filter({ hasText: "Pilcrow Test Submission 3" })
        .getByTestId("submission_actions")
        .click()

    await expect(page.getByTestId("change_status")).toHaveClass(/disabled/)
    await page.getByTestId("change_status").hover()
    await expect(
        page.getByTestId("cannot_change_submission_status_tooltip")
    ).toBeVisible()
    //* should deny a reviewer from accessing rejected submissions

    await expect(page.getByTestId("review")).toHaveClass(/disabled/)
    await page.getByTestId("review").hover()
    await expect(
        page.getByTestId("cannot_access_submission_tooltip")
    ).toBeVisible()
    await page.goto("submission/review/102")
    await expect(page).toHaveURL(/error403/)
    //* should deny a reviewer from accessing submissions requested for resubmission"

    await page.goto("submission/review/101")
    await expect(page).toHaveURL(/error403/)
})
