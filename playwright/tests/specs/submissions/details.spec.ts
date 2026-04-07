import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation, userSearch } from "@helpers/graphql";
import { qSelectItems, expectNotification } from "@helpers/quasar";

test("publication admin: assignment management", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("publicationadministrator@meshresearch.net", "/submission/113/details");
  await page.getByTestId("submitters_list").waitFor();

  await test.step("page is accessible", async () => {
    await checkA11y(page);
  });

  await test.step("assign a reviewer", async () => {
    const reviewersList = page.getByTestId("reviewers_list");
    await userSearch(reviewersList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(reviewersList, "input_user");
    await items.nth(0).click();
    const updatePromise = waitForGQLOperation(page, "UpdateSubmissionReviewers");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");
    await updatePromise;
    await expect(reviewersList.locator(".q-list")).toContainText(
      "Application Administrator",
    );
    await expectNotification(page, "positive");
  });

  await test.step("disallow duplicate reviewer assignment", async () => {
    const reviewersList = page.getByTestId("reviewers_list");
    await userSearch(reviewersList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(reviewersList, "input_user");
    await items.nth(0).click();
    const updatePromise = waitForGQLOperation(page, "UpdateSubmissionReviewers");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");
    await updatePromise;
    await expect(reviewersList.locator(".q-item")).toHaveCount(2);
    await expectNotification(page, "negative");
  });

  await test.step("remove review coordinator", async () => {
    const coordList = page.getByTestId("coordinators_list");
    await Promise.all([
      coordList
        .locator(".q-item")
        .nth(0)
        .getByTestId("button_unassign")
        .click(),
      waitForGQLOperation(page, "UpdateSubmissionReviewCoordinators"),
    ]);
    await expect(coordList.locator(".q-list")).toHaveCount(0);
    await expectNotification(page, "positive");
  });

  await test.step("reviewer cannot be assigned as review coordinator", async () => {
    // App admin is currently a reviewer — assigning as coordinator should fail
    const coordList = page.getByTestId("coordinators_list");
    await userSearch(coordList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(coordList, "input_user");
    await items.first().waitFor();
    await items.first().click();
    const negativePromise = waitForGQLOperation(page, "UpdateSubmissionReviewCoordinators");
    await coordList.getByTestId("button-assign").dispatchEvent("click");
    await negativePromise;
    await expectNotification(page, "negative");
    await coordList.locator("input").first().click();
    await page.keyboard.press("Backspace");
  });

  await test.step("reassign review coordinator", async () => {
    const coordList = page.getByTestId("coordinators_list");
    await userSearch(coordList.locator("input").first(), "reviewCoord");
    const items = await qSelectItems(coordList, "input_user");
    await items.nth(0).click();
    const updatePromise = waitForGQLOperation(page, "UpdateSubmissionReviewCoordinators");
    await coordList.getByTestId("button-assign").dispatchEvent("click");
    await updatePromise;
    await expect(coordList.locator(".q-list")).toContainText(
      "Review Coordinator",
    );
    await expectNotification(page, "positive");
  });

  await test.step("remove added reviewer", async () => {
    const reviewersList = page.getByTestId("reviewers_list");
    await Promise.all([
      reviewersList
        .locator(".q-item")
        .filter({ hasText: "Application Administrator" })
        .getByTestId("button_unassign")
        .click(),
      waitForGQLOperation(page, "UpdateSubmissionReviewers"),
    ]);
    await expectNotification(page, "positive");
  });

  await test.step("review coordinator cannot be assigned as reviewer", async () => {
    // Review coordinator was just reassigned — assigning as reviewer should fail
    const reviewersList = page.getByTestId("reviewers_list");
    await reviewersList.locator("input").first().click();
    await page.keyboard.press("Backspace");
    await userSearch(reviewersList.locator("input").first(), "reviewCoord");
    const items = await qSelectItems(reviewersList, "input_user");
    await items.first().waitFor();
    await items.first().click();
    const updatePromise = waitForGQLOperation(page, "UpdateSubmissionReviewers");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");
    await updatePromise;
    await expectNotification(page, "negative");
    await page.keyboard.press("Escape");
  });
});

test("publication admin: status change comments in activity", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("publicationadministrator@meshresearch.net", "/submission/108/review");

  await test.step("add status change comments", async () => {
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("open_for_review").click();
    await page
      .getByTestId("status_change_comment")
      .fill("first comment from pub admin");
    await Promise.all([
      page.getByTestId("dirtyYesChangeStatus").click(),
      waitForGQLOperation(page, "UpdateSubmissionStatus"),
    ]);
    await expectNotification(page, "positive", "change_status_notify");

    // Reload to get clean DOM after status change dialog
    // See https://github.com/MESH-Research/Pilcrow/issues/2252
    await page.goto("/submission/108/review");
    await page.getByTestId("status-dropdown").waitFor();
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("close_for_review").click();
    await page
      .getByTestId("status_change_comment")
      .fill("second comment from pub admin");
    await Promise.all([
      page.getByTestId("dirtyYesChangeStatus").click(),
      waitForGQLOperation(page, "UpdateSubmissionStatus"),
    ]);
    await expectNotification(page, "positive", "change_status_notify");
  });

  await test.step("comments visible in activity section", async () => {
    await page.goto("/submission/108/details");
    await expect(page.getByTestId("activity_section")).toContainText(
      "first comment from pub admin",
    );
    await expect(page.getByTestId("activity_section")).toContainText(
      "second comment from pub admin",
    );
  });
});

test("review coordinator: assignment management", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewcoordinator@meshresearch.net", "/submission/113/details");

  await test.step("can assign reviewers", async () => {
    const reviewersList = page.getByTestId("reviewers_list");
    await userSearch(reviewersList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(reviewersList, "input_user");
    await items.first().waitFor();
    await items.nth(0).click();
    const updatePromise = waitForGQLOperation(page, "UpdateSubmissionReviewers");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");
    await updatePromise;
    await expect(reviewersList.locator(".q-list")).toContainText(
      "Application Administrator",
    );
    await expectNotification(page, "positive");
  });

  await test.step("invitation form is visible", async () => {
    await expect(page.getByTestId("invitation_form")).toBeVisible();
  });

  await test.step("invite unregistered reviewer", async () => {
    const reviewersList = page.getByTestId("reviewers_list");
    await reviewersList.locator("input").first().clear();
    await userSearch(
      reviewersList.locator("input").first(),
      "scholarlystranger@gmail.com",
    );
    await waitForGQLOperation(page, "SearchUsers");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");

    await expect(reviewersList.locator(".q-list")).toContainText("stranger");
  });

  await test.step("reinvite unconfirmed reviewer", async () => {
    await page.getByTestId("user_unconfirmed").dispatchEvent("click");
    await page.getByTestId("dirtyYesReinviteUser").click();
    await expectNotification(page, "positive");
  });

  await test.step("disallow invitation for invalid email", async () => {
    await page.goto("/submission/113/details");
    const reviewersList = page.getByTestId("reviewers_list");
    await reviewersList.locator("input").first().clear();
    await userSearch(reviewersList.locator("input").first(), "invalidemail");
    await waitForGQLOperation(page, "SearchUsers");
    const invitePromise = waitForGQLOperation(page, "InviteReviewer");
    await reviewersList.getByTestId("button-assign").dispatchEvent("click");
    await invitePromise;
    await expect(reviewersList.locator(".q-item")).toHaveCount(3);
    await expectNotification(page, "negative");
  });
});

test("reviewer: submission details restrictions", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewer@meshresearch.net", "/submission/113/details");

  await test.step("invitation form is hidden", async () => {
    await expect(page.getByTestId("invitation_form")).toHaveCount(0);
  });
});

test("submitter: submission details", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submission/113/details");

  await test.step("update submission title", async () => {
    const gqlPromise = waitForGQLOperation(page, "UpdateSubmissionTitle");
    await page.getByTestId("submission_title").click();
    await page.getByTestId("submission_title_input").fill("Test Input");
    await page.getByTestId("submission_title_input").press("Enter");
    await gqlPromise;
    await expect(page.getByTestId("submission_title")).toContainText(
      "Test Input",
    );
    // TODO: No success notification shown — see MESH-Research/Pilcrow#2254
  });

  await test.step("export button disabled for under-review submission", async () => {
    await expect(page.getByTestId("submission_export_btn")).toBeDisabled();
  });

  await test.step("export button enabled for rejected submission", async () => {
    await page.goto("/submission/102/review");
    await expect(
      page.getByTestId("submission_export_btn"),
    ).toBeEnabled();
  });
});
