import { expect, test } from "@playwright/test"
import { defaultAxeScan, login, resetDb } from "../helpers"

test("submission review reviewer actions", async ({ page, baseURL }) => {
    await resetDb(baseURL)

    await login(page, "reviewer@pilcrow.dev")

    await page.goto("/submission/100/review")

    const { violations } = await defaultAxeScan(page).analyze()
    expect(violations).toHaveLength(0)

    //* Reviewers can add comments
    await page
        .getByTestId("overallCommentEditor")
        .locator(".ProseMirror")
        .fill("This is an overall comment")
    await page
        .getByTestId("overallCommentEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()
    await expect(page.getByTestId("overallComment")).toHaveCount(4)
    await expect(page.getByTestId("overallComment").last()).toHaveText(
        /This is an overall comment/
    )

    //* Reviewers can edit overall comments
    await page
        .getByTestId("overallComment")
        .last()
        .getByTestId("commentActions")
        .click()

    await page.getByTestId("modifyComment").click()

    await page
        .getByTestId("modifyOverallCommentEditor")
        .locator(".ProseMirror")
        .fill("This is an modified overall comment")
    await page
        .getByTestId("modifyOverallCommentEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()

    await expect(page.getByTestId("overallComment").last()).toHaveText(
        /modified overall comment/
    )

    //* Can reply to comments
    const firstOverallComment = page.getByTestId("overallComment").first()
    await firstOverallComment.getByRole("button", { name: "REPLY" }).click()
    await page
        .getByTestId("overallCommentReplyEditor")
        .locator(".ProseMirror")
        .fill("This is a reply to an overall comment")
    await page
        .getByTestId("overallCommentReplyEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()

    await firstOverallComment.getByTestId("showRepliesButton").click()

    await expect(
        firstOverallComment.getByTestId("overallCommentReply")
    ).toHaveCount(1)

    await expect(
        firstOverallComment.getByTestId("overallCommentReply")
    ).toHaveText(/This is a reply to an overall comment/)

    //* Can edit overall comment replies
    await firstOverallComment
        .getByTestId("overallCommentReply")
        .getByTestId("commentActions")
        .click()

    await page.getByTestId("modifyComment").click()

    // modify, submit
    await page
        .getByTestId("modifyOverallCommentReplyEditor")
        .locator(".ProseMirror")
        .fill("This is a modified overall comment reply.")
    await page
        .getByTestId("modifyOverallCommentReplyEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()

    // verify comment includes "This is a modified overall comment reply."
    await expect(
        firstOverallComment.getByTestId("overallCommentReply")
    ).toHaveText(/This is a modified overall comment reply./)

    //* Can reply to replies
    const firstOverallCommentReply = firstOverallComment
        .getByTestId("overallCommentReply")
        .first()

    await firstOverallCommentReply.getByTestId("commentActions").click()
    await page.getByTestId("quoteReply").click()
    await page
        .getByTestId("overallCommentReplyEditor")
        .locator(".ProseMirror")
        .fill("This is a reply to a reply to an overall comment")
    await page
        .getByTestId("overallCommentReplyEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()
    await expect(
        firstOverallComment.getByTestId("overallCommentReply")
    ).toHaveCount(2)

    const content = await page
        .getByTestId("submission-content")
        .locator(".ProseMirror")

    //* Can create inline comment
    const firstInlineComment = page.getByTestId("inlineComment").first()

    await content.evaluate((element) => {
        /* eslint-env browser */
        const selection = window.getSelection()
        const range = document.createRange()
        range.selectNodeContents(element.childNodes[0])
        selection.removeAllRanges()
        selection.addRange(range)
    })
    await page.getByRole("button", { name: "Add Inline Comment" }).click()
    const inlineCommentForm = page.getByTestId("InlineCommentForm").first()
    await expect(inlineCommentForm.getByTestId("criteria-item")).toHaveCount(4)
    await inlineCommentForm
        .getByTestId("criteria-item")
        .filter({ hasText: "Accessibility" })
        .locator(".q-expansion-item__toggle-icon")
        .click()

    await expect(
        inlineCommentForm
            .getByTestId("criteria-item")
            .filter({ hasText: "Accessibility" })
            .getByTestId("criteria-description")
    ).toBeVisible()
    await inlineCommentForm.locator(".ProseMirror").fill("New comment.")
    await inlineCommentForm
        .getByTestId("criteria-item")
        .filter({ hasText: "Accessibility" })
        .getByTestId("criteria-label")
        .click()
    await inlineCommentForm.getByRole("button", { name: "SUBMIT" }).click()

    await expect(firstInlineComment).toHaveText(/New comment./)
    await expect(firstInlineComment).toHaveText(/Accessibility/)

    //* should allow inline comments to be modified

    await firstInlineComment.getByTestId("commentActions").click()
    await page.getByTestId("modifyComment").click()
    await page
        .getByTestId("modifyInlineCommentEditor")
        .locator(".ProseMirror")
        .fill("This is a modified inline comment.")

    await page
        .getByTestId("modifyInlineCommentEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()

    await expect(firstInlineComment).toHaveText(
        /This is a modified inline comment/
    )

    //* Can reply to inline comments

    await firstInlineComment.getByTestId("inlineCommentReplyButton").click()
    await firstInlineComment
        .locator(".ProseMirror")
        .fill("This is an inline comment reply.")
    await firstInlineComment.getByRole("button", { name: "SUBMIT" }).click()

    await content.evaluate(() => {
        /* eslint-env browser */
        const selection = window.getSelection()
        selection.removeAllRanges()
    }) //* Clear selection
    await content.click()

    await firstInlineComment
        .getByRole("button", { name: "Show replies" })
        .click()
    await expect(
        firstInlineComment.getByTestId("inlineCommentReply")
    ).toHaveCount(1)

    await expect(
        firstInlineComment.getByTestId("inlineCommentReply").last()
    ).toHaveText(/This is an inline comment reply./)

    //* should allow inline comment replies to be modified

    await firstInlineComment
        .getByTestId("inlineCommentReply")
        .last()
        .getByTestId("commentActions")
        .click()
    await page.getByTestId("modifyComment").click()
    await page
        .getByTestId("modifyInlineCommentReplyEditor")
        .locator(".ProseMirror")
        .fill("This is a modified inline comment reply.")
    await page
        .getByTestId("modifyInlineCommentReplyEditor")
        .getByRole("button", { name: "SUBMIT" })
        .click()

    await expect(
        firstInlineComment.getByTestId("inlineCommentReply").last()
    ).toHaveText(/This is a modified inline comment reply./)

    //* Make inine comments active when clicking their annotation widget
    await expect(page.getByTestId("comment-widget")).toHaveCount(4)

    await page.getByTestId("comment-widget").nth(0).click()

    await expect(firstInlineComment.locator(".q-card")).toHaveClass(/active/)

    const secondInlineComment = page.getByTestId("inlineComment").nth(1)

    //* Make inline comments active when clicking their annotation
    await page.getByTestId("comment-highlight").nth(1).click()
    await expect(secondInlineComment.locator(".q-card")).toHaveClass(/active/)
    await expect(firstInlineComment.locator(".q-card")).not.toHaveClass(
        /active/
    )

    //* Should deny a reviewer access when a submission is not accepted for review
    await page.goto("/submission/101/review")
    await expect(page).toHaveURL("/error403")

    //* Should not allow editing of comments the user did not create
    await login(page, "applicationadministrator@pilcrow.dev")
    await page.goto("submission/100/review")

    await page
        .getByTestId("overallComment")
        .first()
        .getByTestId("commentActions")
        .click()
    await expect(page.getByTestId("modifyComment")).toHaveCount(0)

    await page
        .getByTestId("inlineComment")
        .first()
        .getByTestId("commentActions")
        .click()
    await expect(page.getByTestId("modifyComment")).toHaveCount(0)
})

test("submission review other user actions", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await login(page, "regularuser@pilcrow.dev")

    //* Submission in draft status can be submitted for review
    await page.goto("submission/104/review")
    await expect(page.getByTestId("submission_status")).toHaveText(/Draft/)
    await page.getByTestId("status-dropdown").click()
    await page.getByTestId("initially_submit").click()
    await page.getByTestId("dirtyYesChangeStatus").click()
})
