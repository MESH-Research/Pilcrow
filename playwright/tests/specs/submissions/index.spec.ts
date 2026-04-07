import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation } from "@helpers/graphql";
import { qSelectOpen, qSelectItems } from "@helpers/quasar";
import type { Page } from "@playwright/test";

async function showAllRecords(page: Page) {
  await page
    .getByTestId("submissions_table")
    .getByText("Records per page")
    .locator("xpath=following-sibling::*")
    .first()
    .click();
  const allOption = page.locator("[role='listbox']").getByText("All");
  await allOption.scrollIntoViewIfNeeded();
  await allOption.click();
}

test("regular user: submissions page", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submissions");
  await page.getByTestId("submissions_title").waitFor();

  await test.step("page is accessible", async () => {
    await checkA11y(page);
  });

  await test.step("navigate to submission creation", async () => {
    await Promise.all([
      loginAs("applicationadministrator@meshresearch.net", "/submissions"),
      waitForGQLOperation(page, "GetPublications"),
    ]);
    await qSelectOpen(page, "publications_select");
    const items = await qSelectItems(page, "publications_select");
    await items.nth(0).click();
    await page.getByTestId("submit_work_btn").click();
    await expect(
      page.getByTestId("submission_create_subheading"),
    ).toContainText("Pilcrow Test Publication 1");
  });
});

test("non-submitter roles: draft submissions are hidden", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();

  await test.step("hidden from reviewers", async () => {
    await loginAs("reviewer@meshresearch.net", "/submissions");
    await expect(page.getByTestId("submissions_table")).not.toContainText(
      "Draft",
    );
  });

  await test.step("hidden from review coordinators", async () => {
    await loginAs("reviewcoordinator@meshresearch.net", "/submissions");
    await expect(page.getByTestId("submissions_table")).not.toContainText(
      "Draft",
    );
  });

  await test.step("hidden from editors", async () => {
    await loginAs("publicationeditor@meshresearch.net", "/submissions");
    await expect(page.getByTestId("submissions_table")).not.toContainText(
      "Draft",
    );
  });
});

test("regular user: export and status actions", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submissions");
  await showAllRecords(page);

  await test.step("export access by status", async () => {
    const expectExportDisabled = async (status: string) => {
      await page
        .getByText(status, { exact: true })
        .first()
        .locator("xpath=ancestor::tr")
        .getByTestId("submission_actions")
        .click();
      await expect(page.getByTestId("export_submission").last()).toHaveClass(
        /disabled/,
      );
      await page.keyboard.press("Escape");
    };

    const expectExportEnabled = async (status: string) => {
      await page
        .getByText(status, { exact: true })
        .first()
        .locator("xpath=ancestor::tr")
        .getByTestId("submission_actions")
        .click();
      await expect(
        page.getByTestId("export_submission").last(),
      ).not.toHaveClass(/disabled/);
      await page.keyboard.press("Escape");
    };

    await expectExportDisabled("Under Review");
    await expectExportDisabled("Initially Submitted");
    await expectExportEnabled("Rejected");
    await expectExportEnabled("Resubmission Requested");
    await expectExportDisabled("Draft");
    await expectExportEnabled("Accepted as Final");
    await expectExportEnabled("Expired");
    await expectExportDisabled("Awaiting Decision");
    await expectExportDisabled("Awaiting Review");
    await expectExportEnabled("Archived");
    await expectExportDisabled("Deleted");
  });

  await test.step("ACCEPTED_AS_FINAL has archive option", async () => {
    await page
      .getByText("Accepted as Final")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await page.getByTestId("change_status").last().click();
    await expect(page.getByTestId("archive").last()).toBeVisible();
    await page.keyboard.press("Escape");
    await page.keyboard.press("Escape");
  });

  await test.step("ARCHIVED has delete option", async () => {
    await page
      .getByText("Archived")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await page.getByTestId("change_status").last().click();
    await expect(page.getByTestId("delete").last()).toBeVisible();
    await page.keyboard.press("Escape");
    await page.keyboard.press("Escape");
  });

  await test.step("DELETED has no status change option", async () => {
    await page
      .getByText("Deleted")
      .locator("xpath=ancestor::tr")
      .getByTestId("submission_actions")
      .click();
    await expect(page.getByTestId("change_status").last()).toHaveClass(
      /disabled/,
    );
    await page.keyboard.press("Escape");
  });
});
