import type { Page } from "@playwright/test";
import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation } from "@helpers/graphql";
import { expectNotification } from "@helpers/quasar";

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

test("reviewer: comments and interactions", async ({
  page,
  resetDatabase,
  seedSubmission,
  loginAs,
}) => {
  await resetDatabase();
  const subId = await seedSubmission({
    status: "UNDER_REVIEW",
    title: "Reviewer Comment Test",
  });
  await loginAs("reviewer@meshresearch.net", `/submission/${subId}/review`);
  await page.getByTestId("submission_review_layout").waitFor();

  await test.step("page accessibility", async () => {
    await checkA11y(page, {
      exclude: ['[data-cy="submission-content"'],
    });
  });

  await test.step("create overall comment", async () => {
    // Empty submit should be ignored
    await page
      .getByTestId("overallCommentEditor")
      .locator("button[type=submit]")
      .click();

    const createPromise = waitForGQLOperation(page, "CreateOverallComment");
    await page
      .getByTestId("overallCommentEditor")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("This is an overall comment.");
    await page
      .getByTestId("overallCommentEditor")
      .locator("button[type=submit]")
      .click();
    await createPromise;
    await expect(page.getByTestId("overallComment")).toHaveCount(1);
    await expect(page.getByTestId("overallComment").last()).toContainText(
      "This is an overall comment.",
    );
  });

  await test.step("reply to overall comment", async () => {
    const replyPromise = waitForGQLOperation(
      page,
      "CreateOverallCommentReply",
    );
    const firstComment = page.getByTestId("overallComment").first();
    await firstComment.getByTestId("overallCommentReplyButton").click();
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("This is a reply to an overall comment.");
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("button[type=submit]")
      .click();
    await replyPromise;
    await firstComment.getByTestId("showRepliesButton").click();
    await expect(firstComment.getByTestId("overallCommentReply")).toHaveCount(
      1,
    );
    await expect(
      firstComment.getByTestId("overallCommentReply").first(),
    ).toContainText("This is a reply to an overall comment.");
  });

  await test.step("quote-reply to a reply", async () => {
    await loginAs("reviewer@meshresearch.net", `/submission/${subId}/review`);
    const commentForQuoteReply = page.getByTestId("overallComment").first();
    await commentForQuoteReply.getByTestId("showRepliesButton").click();
    await commentForQuoteReply
      .getByTestId("overallCommentReply")
      .first()
      .getByTestId("commentActions")
      .click();
    await page.getByTestId("quoteReply").click();
    const replyToReplyPromise = waitForGQLOperation(
      page,
      "CreateOverallCommentReply",
    );
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("This is a reply to a reply.");
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("button[type=submit]")
      .click();
    await replyToReplyPromise;
    await expect(
      commentForQuoteReply.getByTestId("overallCommentReply"),
    ).toHaveCount(2);
  });

  await test.step("export button disabled for under-review submission", async () => {
    await page.goto(`/submission/${subId}/review`);
    await expect(page.getByTestId("submission_export_btn")).toHaveClass(
      /cursor-not-allowed/,
    );
  });
});

test("reviewer: comment ownership and deletion", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewer@meshresearch.net", "/submission/100/review");

  await test.step("cannot modify other users' overall comments", async () => {
    await page
      .getByTestId("overallComment")
      .first()
      .getByTestId("commentActions")
      .click();
    await expect(page.getByTestId("modifyComment")).toHaveCount(0);
    await page.keyboard.press("Escape");
  });

  await test.step("cannot modify other users' inline comments", async () => {
    await ensureInlineCommentsOpen(page);
    await page
      .getByTestId("inlineComment")
      .nth(1)
      .getByTestId("commentActions")
      .click();
    await expect(page.getByTestId("modifyComment")).toHaveCount(0);
    await page.keyboard.press("Escape");
  });

  await test.step("create own overall comment", async () => {
    const createPromise = waitForGQLOperation(page, "CreateOverallComment");
    const refetchPromise = waitForGQLOperation(page, "GetSubmissionReview");
    await page
      .getByTestId("overallCommentEditor")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("Reviewer comment.");
    await page
      .getByTestId("overallCommentEditor")
      .locator("button[type=submit]")
      .click();
    await createPromise;
    await refetchPromise;
    await expect(
      page.getByTestId("overallComment").filter({ hasText: "Reviewer comment." }),
    ).toBeVisible();
  });

  await test.step("modify own overall comment", async () => {
    await page.goto("/submission/100/review");
    const ownComment = page
      .getByTestId("overallComment")
      .filter({ hasText: "Reviewer comment." });
    await ownComment.getByTestId("commentActions").click();
    await page.getByTestId("modifyComment").click();

    const editor = page
      .getByTestId("modifyOverallCommentEditor")
      .locator("[contenteditable]");
    await editor.click({ clickCount: 3 });
    await page.keyboard.type("Modified reviewer comment.");

    const updatePromise = waitForGQLOperation(page, "UpdateOverallComment");
    const refetchPromise = waitForGQLOperation(page, "GetSubmissionReview");
    await page
      .getByTestId("modifyOverallCommentEditor")
      .locator("button[type=submit]")
      .click();
    await updatePromise;
    await refetchPromise;
    await expect(
      page.getByTestId("overallComment").filter({ hasText: "Modified reviewer comment." }),
    ).toBeVisible();
  });

  await test.step("reply to own comment then delete it", async () => {
    const ownComment = page
      .getByTestId("overallComment")
      .filter({ hasText: "Modified reviewer comment." });
    await ownComment.getByTestId("overallCommentReplyButton").click();
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("Reply before delete.");
    await Promise.all([
      page
        .getByTestId("overallCommentReplyEditor")
        .first()
        .locator("button[type=submit]")
        .click(),
      waitForGQLOperation(page, "CreateOverallCommentReply"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);

    await ownComment.getByTestId("commentActions").click();
    await page.getByTestId("deleteComment").click();
    await Promise.all([
      page.getByTestId("dirtyDelete").click(),
      waitForGQLOperation(page, "DeleteOverallComment"),
      waitForGQLOperation(page, "GetSubmissionReview"),
    ]);
    await expect(
      page.getByTestId("overallComment").filter({ hasText: "This comment has been deleted" }),
    ).toBeVisible();
  });

  await test.step("create and modify own reply", async () => {
    const replyPromise = waitForGQLOperation(
      page,
      "CreateOverallCommentReply",
    );
    const refetchPromise = waitForGQLOperation(page, "GetSubmissionReview");
    const firstComment = page.getByTestId("overallComment").first();
    await firstComment.getByTestId("overallCommentReplyButton").click();
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("Reviewer reply.");
    await page
      .getByTestId("overallCommentReplyEditor")
      .first()
      .locator("button[type=submit]")
      .click();
    await replyPromise;
    await refetchPromise;

    await firstComment.getByTestId("showRepliesButton").click();
    await page
      .getByTestId("overallCommentReply")
      .first()
      .getByTestId("commentActions")
      .click();
    await page.getByTestId("modifyComment").click();

    const replyEditor = page
      .getByTestId("modifyOverallCommentReplyEditor")
      .locator("[contenteditable]");
    await replyEditor.click({ clickCount: 3 });
    await page.keyboard.type("Modified reviewer reply.");

    const updatePromise = waitForGQLOperation(page, "UpdateOverallCommentReply");
    const refetchPromise2 = waitForGQLOperation(page, "GetSubmissionReview");
    await page
      .getByTestId("modifyOverallCommentReplyEditor")
      .locator("button[type=submit]")
      .click();
    await updatePromise;
    await refetchPromise2;
    await expect(
      page.getByTestId("overallCommentReply").first(),
    ).toContainText("Modified reviewer reply.");
  });
});

test("review coordinator: status management", async ({
  page,
  resetDatabase,
  seedSubmission,
  loginAs,
}) => {
  test.setTimeout(60_000);
  await resetDatabase();
  const subInitial = await seedSubmission({ status: "INITIALLY_SUBMITTED" });
  const subAwaiting = await seedSubmission({ status: "AWAITING_REVIEW" });
  const subRejected = await seedSubmission({ status: "REJECTED" });
  const subResubmission = await seedSubmission({
    status: "RESUBMISSION_REQUESTED",
  });
  const subAccepted = await seedSubmission({ status: "ACCEPTED_AS_FINAL" });
  const subDraft = await seedSubmission({
    status: "DRAFT",
    withContent: false,
  });

  await test.step("reviewer cannot change submission status", async () => {
    await loginAs("reviewer@meshresearch.net", `/submission/${subInitial}/review`);
    await expect(page.getByTestId("status-dropdown")).toHaveCount(0);
  });

  await test.step("reviewer can access before acceptance", async () => {
    await expect(page).not.toHaveURL(/\/error403/);
  });

  await test.step("accept submission for review", async () => {
    await loginAs("reviewcoordinator@meshresearch.net", `/submission/${subInitial}/review`);
    await page.getByTestId("status-dropdown").waitFor();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Initially Submitted",
    );
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("open_for_review").click();
    await page.getByTestId("dirtyYesChangeStatus").click();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Under Review",
    );
  });

  await test.step("reviewer can access after acceptance", async () => {
    await loginAs("reviewer@meshresearch.net", `/submission/${subInitial}/review`);
    await expect(page).not.toHaveURL(/\/error403/);
  });

  await test.step("open submission for review", async () => {
    await loginAs("reviewcoordinator@meshresearch.net", `/submission/${subAwaiting}/review`);
    await page.getByTestId("status-dropdown").waitFor();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Awaiting Review",
    );
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("open_for_review").click();
    await page.getByTestId("dirtyYesChangeStatus").click();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Under Review",
    );
  });

  await test.step("close review and verify decision options", async () => {
    await page.goto(`/submission/${subAwaiting}/review`);
    await expect(page.getByTestId("submission_status")).toContainText(
      "Under Review",
    );
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("close_for_review").click();
    await page.getByTestId("dirtyYesChangeStatus").click();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Awaiting Decision",
    );
    await page.keyboard.press("Escape");
    await page.getByTestId("status-dropdown").click();
    await expect(page.getByTestId("decision_options")).toBeVisible();
    // Close submenu then menu (opened to inspect, not to act)
    await page.keyboard.press("Escape");
    await page.keyboard.press("Escape");
  });

  await test.step("rejected submissions have no decision options", async () => {
    await page.goto(`/submission/${subRejected}/review`);
    await expect(page.getByTestId("submission_status")).toContainText(
      "Rejected",
    );
    await expect(page.getByTestId("decision_options")).toHaveCount(0);
  });

  await test.step("resubmission-requested submissions have no decision options", async () => {
    await page.goto(`/submission/${subResubmission}/review`);
    await expect(page.getByTestId("submission_status")).toContainText(
      "Resubmission Requested",
    );
    await expect(page.getByTestId("decision_options")).toHaveCount(0);
  });

  await test.step("archive accepted submission", async () => {
    await page.goto(`/submission/${subAccepted}/review`);
    await expect(page.getByTestId("submission_status")).toContainText(
      "Accepted as Final",
    );
    await page.getByTestId("status-dropdown").click();
    await expect(page.getByTestId("archive")).toBeVisible();
    await page.getByTestId("archive").click();
    await page
      .getByTestId("dirtyYesChangeStatus")
      .waitFor({ state: "visible" });
    await page.getByTestId("dirtyYesChangeStatus").click();
    await expectNotification(page, "positive", "change_status_notify");
    await expect(page.getByTestId("submission_status")).toContainText(
      "Archived",
    );
  });

  // TODO: Menu should auto-close after a status change is confirmed
  // https://github.com/MESH-Research/Pilcrow/issues/2252
  await test.step("delete archived submission", async () => {
    await page.keyboard.press("Escape");
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("delete").click();
    await page
      .getByTestId("dirtyYesChangeStatus")
      .waitFor({ state: "visible" });
    await page.getByTestId("dirtyYesChangeStatus").click();
    await expect(page.getByTestId("submission_status")).toContainText(
      "Deleted",
    );
    await expect(page.getByTestId("status-dropdown")).toHaveCount(0);
  });

  await test.step("draft with no content shows explanation", async () => {
    await loginAs("regularuser@meshresearch.net", `/submission/${subDraft}/review`);
    await expect(page.getByTestId("explanation")).toBeVisible();
    await page.getByTestId("draft_btn").click();
    await expect(page).toHaveURL(
      new RegExp(`/submission/${subDraft}/draft`),
    );
  });
});

test("reviewer: footnote scrolling", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewer@meshresearch.net", "/submission/112/review");

  await test.step("scroll to footnote and back", async () => {
    const fnref = page.locator("#fnref1");
    const box = await fnref.boundingBox();
    expect(box!.y).toBeGreaterThan(-1);

    await fnref.click();
    await expect
      .poll(async () => (await fnref.boundingBox())!.y)
      .toBeLessThan(0);

    await page.locator("a[href='#fnref1']").click();
    await expect
      .poll(async () => (await fnref.boundingBox())!.y)
      .toBeGreaterThan(-1);
  });

  await test.step("page accessibility", async () => {
    await checkA11y(page, {
      exclude: ['[data-cy="submission-content"'],
    });
  });
});

test("review coordinator: scroll to comments", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewcoordinator@meshresearch.net", "/submission/100/review");

  await test.step("scroll to 'View Overall Comments'", async () => {
    const btn = page.getByTestId("view_overall_comments");
    const initialY = (await btn.boundingBox())!.y;
    expect(initialY).toBeGreaterThan(0);

    await btn.click();
    await expect.poll(async () => (await btn.boundingBox())!.y).toBeLessThan(0);
  });

  await test.step("scroll to 'Add New Overall Comment'", async () => {
    await page.goto("/submission/100/review");
    const btn = page.getByTestId("new_overall_comment");
    const initialY = (await btn.boundingBox())!.y;
    expect(initialY).toBeGreaterThan(0);

    await btn.click();
    await expect.poll(async () => (await btn.boundingBox())!.y).toBeLessThan(0);
  });
});
