import path from "path";
import { fileURLToPath } from "url";
import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation } from "@helpers/graphql";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

test("regular user: draft content management", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submission/104/draft");

  await test.step("add content from text input", async () => {
    await page.getByTestId("todo_go_btn").click();
    await page.getByTestId("enter_text_option").click();
    await page
      .getByTestId("content_editor")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("Test Content");
    await page.getByTestId("submit_entered_text_btn").click();
    await page.getByTestId("content_submit_success_btn").click();
    await expect(page.getByTestId("todo_preview_btn").nth(0)).toContainText(
      "Preview",
    );
    await expect(page.getByTestId("todo_content_btn").nth(0)).toContainText(
      "Edit",
    );
  });

  await test.step("page is accessible after adding content", async () => {
    await checkA11y(page);
  });
});

test("regular user: draft file upload", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submission/104/draft");

  await test.step("add content from file upload", async () => {
    await page.getByTestId("todo_go_btn").click();
    await page.getByTestId("upload_option").click();
    await page
      .getByTestId("file_picker")
      .setInputFiles(
        path.resolve(__dirname, "../../fixtures/test.txt"),
      );
    await page.getByTestId("submit_upload_btn").click();
    await page.getByTestId("content_submit_success_btn").click();
    await expect(page.getByTestId("todo_preview_btn").nth(0)).toContainText(
      "Preview",
    );
    await expect(page.getByTestId("todo_content_btn").nth(0)).toContainText(
      "Edit",
    );
  });

  await test.step("page is accessible after upload", async () => {
    await checkA11y(page);
  });
});

test("regular user: draft preview and submission", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/submission/111/draft");

  await test.step("preview a draft submission", async () => {
    await page.getByTestId("todo_preview_btn").click();
    await expect(page).toHaveURL(/\/111\/preview/);
    await checkA11y(page, {
      exclude: ['[data-cy="submission-content"'],
    });
  });

  await test.step("submit for review", async () => {
    await page.goto("/submission/111/draft");
    await page.getByTestId("submit_for_review_btn").click();
    const gqlPromise = waitForGQLOperation(page, "UpdateSubmissionStatus");
    await page.getByTestId("dirtyYesChangeStatus").click();
    await gqlPromise;
  });

  await test.step("editor can access submitted submission", async () => {
    await loginAs("publicationeditor@meshresearch.net", "/submission/111/view");
    await expect(page).not.toHaveURL(/\/error403/);
  });
});
