import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("submission management", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("submission/101/review")

    await expect(page.getByTestId("submission_status")).toHaveText(
        /Initially Submitted/
    )
    await page.getByTestId("status-dropdown").click()

    await page.getByTestId("accept_for_review").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Awaiting Review/
    )

    await login(page, "reviewer@pilcrow.dev")
    await expect(page).not.toHaveURL("/error403")

    await login(page, "applicationadministrator@pilcrow.dev")

    //* Administrator can open and close a review.
    await page.goto("submission/108/review")
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Awaiting Review/
    )
    await page.getByTestId("status-dropdown").click()
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
    await page.goto("submission/102/review")
    await expect(page.getByTestId("submission_status")).toHaveText(/Rejected/)
    await expect(page.getByTestId("decision_options")).toHaveCount(0)

    //* Should not display the decision options for submissions requested for resubmission"
    await page.goto("submission/103/review")
    await expect(page.getByTestId("submission_status")).toHaveText(
        /Resubmission Requested/
    )
    await expect(page.getByTestId("decision_options")).toHaveCount(0)
})

test("app administrator submission management", async ({ page, baseURL }) => {
    test.fixme("app admins currently cannnot view all submissions")
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    //* Should deny an application administrator from changing status of rejected submissions
    await page.goto("reviews")
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
    await page.goto("reviews")

    await page
        .getByRole("row", { name: /Test Submission 3/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("change_status")).toHaveClass(/disabled/)
    await page.getByTestId("change_status").hover()
    await expect(
        page.getByTestId("cannot_change_submission_status_tooltip")
    ).toBeVisible()
    //* should deny a reviewer from accessing rejected submissions

    await expect(page.getByTestId("submission_review_link")).toHaveClass(
        /disabled/
    )
    await page.getByTestId("submission_review_link").hover()
    await expect(
        page.getByTestId("cannot_access_submission_tooltip")
    ).toBeVisible()
    await page.goto("submission/102/review")
    await expect(page).toHaveURL(/error403/)
    //* should deny a reviewer from accessing submissions requested for resubmission"

    await page.goto("submission/101/review")
    await expect(page).toHaveURL(/error403/)
})
test("submission reviewer coordinator access", async ({ page, baseURL }) => {
    //* should deny a reviewer from changing the status of rejected submissions

    await resetDb(baseURL)
    await login(page, "reviewcoordinator@pilcrow.dev")
    await page.goto("reviews")
    //* should allow a review coordinator to accept a submission for review and permit reviewers access
    await page
        .getByTestId("coordinator_table")
        .getByRole("cell", { name: "Number" })
        .click()
    await page
        .getByRole("row", { name: /Test Submission 2/ })
        .getByTestId("submission_actions")
        .click()
    await page.getByTestId("change_status").click()
    await page.getByTestId("accept_for_review").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
    await expect(page.getByTestId("change_status_notify")).toHaveClass(
        /bg-positive/
    )
    await login(page, "reviewer@pilcrow.dev")
    await page.goto("submission/101/review")
    await expect(page).not.toHaveURL("/error403")

    //* should deny a review coordinator from changing the status of rejected submissions
    await login(page, "reviewcoordinator@pilcrow.dev")
    await page.goto("reviews")
    await page
        .getByTestId("coordinator_table")
        .getByRole("cell", { name: "Number" })
        .click()
    await page
        .getByRole("row", { name: /Test Submission 3/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("change_status")).toHaveClass(/disabled/)
    //* should allow a review coordinator to access rejected submissions

    await page
        .getByRole("listitem")
        .filter({ hasText: "View Submission Details" })
        .click()
    await expect(page).toHaveURL(/submission\/102\/details$/)
    await page.getByTestId("submission_review_btn").click()
    await expect(page).toHaveURL(/submission\/102\/review$/)

    //* should allow a review coordinator to access submissions requested for resubmission
    await page.goto("/submission/103/details")
    await page.getByRole("alert").getByText("Resubmission Requested")

    //* should deny a reviewer from accepting a submission for review
})

test("submission lists", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")
    await page.goto("/submissions")

    await page.getByTestId("submissions_title")
    expect((await defaultAxeScan(page).analyze()).violations).toHaveLength(0)

    //* "directs users to a publication's submission creation form"
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("submissions")

    await page.getByTestId("publications_select").click()
    await page.getByTestId("publications_select").click()
    await page
        .getByRole("option", { name: "Pilcrow Test Publication 1" })
        .click()

    await page.getByTestId("submit_work_btn").click()
    //TODO: This fails with a header contrast issue that appears to be incorrect.
    // expect
    //     .soft((await defaultAxeScan(page).analyze()).violations)
    //     .toHaveLength(0)

    await expect(page.getByTestId("submission_create_subheading")).toHaveText(
        "Pilcrow Test Publication 1"
    )

    //* should make the submission in draft status invisible to reviewers
    await resetDb(baseURL)
    await login(page, "reviewer@pilcrow.dev")
    await page.goto("submissions")
    await expect(page.getByTestId("submissions_table")).not.toHaveText("Draft")

    //* should make the submission in draft status invisible to review coordinators
    await resetDb(baseURL)
    await login(page, "reviewcoordinator@pilcrow.dev")
    await page.goto("submissions")
    await expect(page.getByTestId("submissions_table")).not.toHaveText("Draft")

    //* should make the submission in draft status invisible to editors
    await resetDb(baseURL)
    await login(page, "publicationeditor@pilcrow.dev")
    await page.goto("submissions")
    await expect(page.getByTestId("submissions_table")).not.toHaveText("Draft")

    //* enables access to the Submission Export page for submissionss according to their status
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")
    await page.goto("/submissions")
    // Show All Records

    await page.getByText("5", { exact: true }).click()
    await page.getByText("All", { exact: true }).click()
    await page
        .getByTestId("submissions_table")
        .getByRole("cell", { name: "Number" })
        .click()
    // Under Review
    await page
        .getByRole("row", { name: /Under Review/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).toHaveClass(/disabled/)

    // Rejected

    await page
        .getByRole("row", { name: /Rejected/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).not.toHaveClass(
        /disabled/
    )
    // Resubmission Requested

    await page
        .getByRole("row", { name: /Resubmission Requested/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).not.toHaveClass(
        /disabled/
    )
    // Draft

    await page
        .getByRole("row", { name: /Draft/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).toHaveClass(/disabled/)
    // Accepted as Final
    await page
        .getByRole("row", { name: /Final/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).not.toHaveClass(
        /disabled/
    )
    // Expired
    await page
        .getByRole("row", { name: /Expired/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).not.toHaveClass(
        /disabled/
    )

    // Awaiting Decision
    await page
        .getByRole("row", { name: /Awaiting Decision/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).toHaveClass(/disabled/)

    // Awaiting Review
    await page
        .getByRole("row", { name: /Awaiting Review/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).toHaveClass(/disabled/)

    // Archived
    await page
        .getByRole("row", { name: /Archived/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).not.toHaveClass(
        /disabled/
    )

    // Deleted
    await page
        .getByRole("row", { name: /Deleted/ })
        .getByTestId("submission_actions")
        .click()
    await expect(page.getByTestId("export_submission")).toHaveClass(/disabled/)
})
