import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { expectNotification } from "@helpers/quasar";

test("app admin: submission creation", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", "/publication/1/create");
  await page.getByTestId("create_submission_form").waitFor({ timeout: 10_000 });

  await test.step("page is accessible", async () => {
    await checkA11y(page);
  });

  await test.step("title must not exceed 512 characters", async () => {
    const name520 = "1234567890".repeat(52);
    await page.getByTestId("new_submission_title_input").fill(name520);
    await page.getByTestId("acknowledgement_checkbox").click();
    await page.getByTestId("create_submission_btn").click();
    await expect(
      page
        .getByTestId("new_submission_title_input")
        .locator("xpath=ancestor::label"),
    ).toHaveClass(/q-field--error/);
    await expect(page.getByTestId("create_submission_form")).toContainText(
      "The maximum length has been exceeded for the title",
    );
    await expectNotification(page, "negative", "submission_create_notify");
  });

  await test.step("title must not be empty", async () => {
    await page.getByTestId("new_submission_title_input").clear();
    await page.getByTestId("create_submission_btn").click();
    await expect(
      page
        .getByTestId("new_submission_title_input")
        .locator("xpath=ancestor::label"),
    ).toHaveClass(/q-field--error/);
    await expect(page.getByTestId("create_submission_form")).toContainText(
      "A title is required to create a submission.",
    );
    await expectNotification(page, "negative", "submission_create_notify");
  });

  await test.step("acknowledgement must be checked", async () => {
    await page.getByTestId("new_submission_title_input").fill("Test Submission");
    // Checkbox was toggled on in first step, toggle it off
    await page.getByTestId("acknowledgement_checkbox").click();
    await page.getByTestId("create_submission_btn").click();
    await expect(
      page
        .getByTestId("acknowledgement_checkbox")
        .locator("xpath=ancestor::label"),
    ).toHaveClass(/q-field--error/);
    await expect(page.getByTestId("create_submission_form")).toContainText(
      "You must acknowledge you have read and understand the guidelines.",
    );
    await expectNotification(page, "negative", "submission_create_notify");
  });

  await test.step("successful submission creation", async () => {
    await page.getByTestId("acknowledgement_checkbox").click();
    await page.getByTestId("create_submission_btn").click();
    await expect(page.getByTestId("submission_title")).toContainText(
      "Test Submission",
    );
  });
});
