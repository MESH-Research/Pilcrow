import type { Page } from "@playwright/test";
import { test, expect } from "@fixtures/base";
import { waitForGQLOperation } from "@helpers/graphql";

const SUBMISSION_ID = 114;

/**
 * Ensure inline comments panel is visible.
 * On wide viewports it's open by default; on narrow ones we toggle it.
 */
async function ensureInlineCommentsOpen(page: Page) {
  await page.getByTestId("submission-content").waitFor({ state: "visible" });
  const visible = await page
    .getByTestId("inlineComment")
    .first()
    .isVisible()
    .catch(() => false);
  if (!visible) {
    await page.getByTestId("toggleInlineCommentsButton").click();
    await page
      .getByTestId("inlineComment")
      .first()
      .waitFor({ state: "visible" });
  }
}

test("reviewer: inline comment interactions", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewer@meshresearch.net", `/submission/${SUBMISSION_ID}/review`);
  await page.getByTestId("submission_review_layout").waitFor();
  await ensureInlineCommentsOpen(page);

  await test.step("submit inline comment reply", async () => {
    const firstComment = page.getByTestId("inlineComment").first();
    await firstComment.getByTestId("showRepliesButton").click();
    await firstComment.getByTestId("inlineCommentReplyButton").click();
    await firstComment
      .getByTestId("inlineCommentReplyEditor")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("This is an inline comment reply.");
    await Promise.all([
      firstComment
        .getByTestId("inlineCommentReplyEditor")
        .locator("button[type=submit]")
        .click(),
      waitForGQLOperation(page, "CreateInlineCommentReply"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);
    await expect(firstComment.getByTestId("inlineCommentReply")).toHaveCount(2);
    await expect(
      firstComment.getByTestId("inlineCommentReply").last(),
    ).toContainText("This is an inline comment reply.");
  });

  await test.step("bubble widgets activate inline comments", async () => {
    await page.goto(`/submission/${SUBMISSION_ID}/review`);
    await ensureInlineCommentsOpen(page);
    const widgetCount = await page.getByTestId("comment-widget").count();
    expect(widgetCount).toBeGreaterThan(0);

    for (let i = 0; i < widgetCount; i++) {
      await page.getByTestId("comment-widget").nth(i).click();
      await expect(
        page.getByTestId("inlineComment").nth(i).locator("> .q-card"),
      ).toHaveClass(/active/);
      // At 1920x1080 the drawer is always open, no backdrop to dismiss
    }
  });

  await test.step("highlights activate inline comments", async () => {
    await page.goto(`/submission/${SUBMISSION_ID}/review`);
    await ensureInlineCommentsOpen(page);
    const highlightCount = await page.getByTestId("comment-highlight").count();

    for (let i = 0; i < highlightCount; i++) {
      await page.getByTestId("comment-highlight").nth(i).click();
      await expect(
        page.getByTestId("inlineComment").locator("> .q-card.active"),
      ).toHaveCount(1);
      // At 1920x1080 the drawer is always open, no backdrop to dismiss
    }
  });
});

test("reviewer: inline comment modification", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", `/submission/${SUBMISSION_ID}/review`);
  await ensureInlineCommentsOpen(page);

  await test.step("modify inline comment", async () => {
    await page
      .getByTestId("inlineComment")
      .first()
      .getByTestId("commentActions")
      .click();
    await page.getByTestId("modifyComment").click();

    await page
      .getByTestId("comment-editor")
      .first()
      .locator("[contenteditable]")
      .click({ clickCount: 3 });
    await page.keyboard.type("This is a modified inline comment.");
    // Toggle off the last criteria, toggle on the first to keep at least one selected
    await page.getByTestId("criteria-item").last().click();
    await page.getByTestId("criteria-item").first().click();
    await Promise.all([
      page
        .getByTestId("modifyInlineCommentEditor")
        .locator("button[type=submit]")
        .click(),
      waitForGQLOperation(page, "UpdateInlineComment"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);
    await expect(page.getByTestId("inlineComment").first()).toContainText(
      "This is a modified inline comment.",
    );
  });

  await test.step("modify inline comment reply", async () => {
    const secondComment = page.getByTestId("inlineComment").nth(1);
    await secondComment.getByTestId("inlineCommentReplyButton").click();

    // Create a reply first
    await secondComment
      .locator("[data-cy='inlineCommentReplyEditor'] [contenteditable]")
      .click();
    await page.keyboard.type("This is a new inline comment reply.");
    await Promise.all([
      secondComment
        .locator("[data-cy='inlineCommentReplyEditor'] button[type=submit]")
        .click(),
      waitForGQLOperation(page, "CreateInlineCommentReply"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);

    // Modify the reply
    await secondComment.getByTestId("showRepliesButton").click();
    await secondComment
      .getByTestId("inlineCommentReply")
      .last()
      .getByTestId("commentActions")
      .click();
    await page.getByTestId("modifyComment").click();
    await page
      .getByTestId("modifyInlineCommentReplyEditor")
      .locator("[contenteditable]")
      .click({ clickCount: 3 });
    await page.keyboard.type("This is a modified inline comment reply.");
    await Promise.all([
      page
        .getByTestId("modifyInlineCommentReplyEditor")
        .locator("button[type=submit]")
        .click(),
      waitForGQLOperation(page, "UpdateInlineCommentReply"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);
    await expect(
      secondComment.getByTestId("inlineCommentReply").last(),
    ).toContainText("This is a modified inline comment reply.");
  });

  await test.step("delete inline comment", async () => {
    await page
      .getByTestId("inlineComment")
      .first()
      .getByTestId("commentActions")
      .click();
    await page.getByTestId("deleteComment").click();
    await Promise.all([
      page.getByTestId("dirtyDelete").click(),
      waitForGQLOperation(page, "DeleteInlineComment"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);
    await expect(page.getByTestId("inlineComment").first()).toContainText(
      "This comment has been deleted",
    );
  });
});
