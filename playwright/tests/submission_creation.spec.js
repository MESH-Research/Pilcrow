import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("submission creation", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")
    await page.goto("publication/1/create")

    let { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    await page
        .getByTestId("new_submission_title_input")
        .fill("Submission from Cypress")

    await page.getByTestId("create_submission_btn").click()

    await page.getByTestId("button_dismiss_notify").click()
    await page.getByText(
        "You must acknowledge you have read and understand the guidelines."
    )

    await page.getByTestId("acknowledgement_checkbox").click()
    await page.getByTestId("create_submission_btn").click()
    await expect(page).toHaveURL(/[0-9]+\/draft$/)
    const draftSubmission = page.url().match(/([0-9]+)\/draft$/)[1]
    await expect(page.getByTestId("submission_title")).toHaveText(
        /Submission from Cypress/
    )

    //* prevents submission creation when the title exceeds the maximum length"

    await page.goto("publication/1/create")
    await page
        .getByTestId("new_submission_title_input")
        .fill("1234567890".repeat(52))
    await page.getByTestId("new_submission_title_input").press("Enter")
    await page.getByText("The maximum length has been exceeded for the title.")

    //* Allows content to be added from a text input

    await page.goto("/submission/104/draft")
    await page.getByTestId("todo_go_btn").click()
    await page.getByTestId("paste_option").click()
    await page.locator(".q-editor__content").fill("Hello World")

    await page.getByTestId("submit_paste_btn").click()
    await page.getByTestId("content_submit_success_btn").click()
    await expect(page.getByTestId("todo_done_btn").first()).toHaveText(/Done/)
    await expect(page.getByTestId("todo_icon").first()).toHaveClass(
        /text-positive/
    )

    //* allows content to be added from a file upload
    await page.goto(`/submission/${draftSubmission}/draft`)
    await page.getByTestId("todo_go_btn").click()
    await page.getByTestId("upload_option").click()
    await page
        .locator('[data-cy="file_picker"]')
        .setInputFiles("fixtures/test.txt")

    await page.getByTestId("submit_upload_btn").click()
    await page.getByTestId("content_submit_success_btn").click()

    await expect(page.getByTestId("todo_done_btn").first()).toHaveText(/Done/)
    await expect(page.getByTestId("todo_icon").first()).toHaveClass(
        /text-positive/
    )
})
