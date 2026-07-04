import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation } from "@helpers/graphql";
import type { Page } from "@playwright/test";

/** Show all records in a table by clicking the "Records per page" → "All" selector. */
async function showAllRecords(table: ReturnType<Page["getByTestId"]>) {
  await table
    .getByText("Records per page")
    .locator("xpath=following-sibling::*")
    .first()
    .click();
  await table.page().locator("[role='listbox']").getByText("All").click();
}

test("review coordinator: reviews page", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewcoordinator@meshresearch.net", "/reviews");
  await page.getByTestId("submission_link_desktop").first().waitFor();

  await test.step("page accessibility", async () => {
    await checkA11y(page);
  });

  await test.step("accept a submission for review", async () => {
    await showAllRecords(page.getByTestId("coordinator_table"));

    await page
      .getByText("Initially Submitted")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await page.getByTestId("change_status").click();
    await page.getByTestId("accept_for_review").click();
    const gqlPromise = waitForGQLOperation(page, "UpdateSubmissionStatus");
    await page.getByTestId("dirtyYesChangeStatus").click();
    await gqlPromise;
    const notify = page.getByTestId("change_status_notify");
    await expect(notify).toBeVisible();
    await expect(notify).toHaveClass(/bg-positive/);
  });

  await test.step("cannot change status of rejected submissions", async () => {
    await page.goto("/reviews");
    await showAllRecords(page.getByTestId("coordinator_table"));

    await page
      .getByText("Rejected")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await expect(page.getByTestId("change_status")).toHaveClass(/disabled/);
    await page.getByTestId("change_status_item_section").hover();
    await expect(
      page.getByTestId("cannot_change_submission_status_tooltip"),
    ).toBeVisible();
  });

  await test.step("access rejected submission details and review", async () => {
    // Menu is still open from previous step
    await page.getByTestId("submission_details_link").click();
    await expect(page).toHaveURL(/\/submission\/102\/details/);
    await page.getByTestId("submission_review_btn").click();
    await expect(page).toHaveURL(/\/submission\/102\/review/);
    await expect(page.getByTestId("submission_title")).toBeVisible();
  });

  await test.step("access resubmission-requested submission", async () => {
    await page.goto("/reviews");
    await showAllRecords(page.getByTestId("coordinator_table"));

    await page
      .getByText("Resubmission Requested")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await page.getByTestId("submission_details_link").click();
    await expect(page).toHaveURL(/\/submission\/103\/details/);
    await expect(page.getByTestId("submission_title")).toBeVisible();
  });
});

test("reviewer: reviews page", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("reviewer@meshresearch.net", "/reviews");
  await showAllRecords(page.getByTestId("reviewer_table"));

  await test.step("cannot see initially submitted submissions", async () => {
    await expect(page.getByTestId("reviewer_table")).not.toContainText(
      "Initially Submitted",
    );
  });

  await test.step("cannot change status of resubmission-requested submissions", async () => {
    await page
      .getByText("Resubmission Requested")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await expect(page.getByTestId("change_status")).toHaveCount(0);
    await page.keyboard.press("Escape");
  });

  await test.step("cannot change status of rejected submissions", async () => {
    await page
      .getByText("Rejected")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await expect(page.getByTestId("change_status")).toHaveCount(0);
  });

  await test.step("cannot access rejected submissions", async () => {
    // Menu is still open from previous step
    await expect(page.getByTestId("submission_review_link").last()).toHaveClass(
      /disabled/,
    );
    await page.getByTestId("submission_review_link").last().hover();
    await expect(
      page.getByTestId("cannot_access_submission_tooltip"),
    ).toBeVisible();
    await page.goto("/submission/102/review");
    await expect(page).toHaveURL(/\/error403/);
  });

  await test.step("cannot access resubmission-requested submissions", async () => {
    await page.goto("/reviews");
    await showAllRecords(page.getByTestId("reviewer_table"));
    await page
      .getByText("Resubmission Requested")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await expect(page.getByTestId("submission_review_link")).toHaveClass(
      /disabled/,
    );
    await page.getByTestId("submission_review_link").hover();
    await expect(
      page.getByTestId("cannot_access_submission_tooltip"),
    ).toBeVisible();
    await page.goto("/submission/103/review");
    await expect(page).toHaveURL(/\/error403/);
  });
});
