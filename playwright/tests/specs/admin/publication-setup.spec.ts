import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";
import { waitForGQLOperation, userSearch } from "@helpers/graphql";
import { expectNotification, qSelectItems } from "@helpers/quasar";

test("publication admin: access control", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("publicationadministrator@meshresearch.net", "/publication/1/setup/basic");

  await test.step("can access own publication setup", async () => {
    await expect(page).not.toHaveURL(/\/error403/);
  });

  await test.step("cannot access other publication setup", async () => {
    await page.goto("/publication/2/setup/basic");
    await expect(page).toHaveURL(/\/error403/);
  });

  await test.step("configure button visible for own publication", async () => {
    await page.goto("/publication/1");
    await expect(page.getByTestId("configure_button")).toBeVisible();
  });

  await test.step("configure button hidden for other publication", async () => {
    await page.goto("/publication/2");
    await expect(page.getByTestId("configure_button")).toHaveCount(0);
  });
});

test("app admin: user management", async ({ page, resetDatabase, loginAs }) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", "/publication/3/setup/users");

  const adminsList = page.getByTestId("admins_list");
  const editorsList = page.getByTestId("editors_list");

  await test.step("assign and remove an administrator", async () => {
    let gqlPromise = waitForGQLOperation(page, "UpdatePublicationAdmins");
    await userSearch(adminsList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(adminsList, "input_user");
    await items.first().waitFor({ state: "visible" });
    await items.nth(0).click();
    await adminsList.getByTestId("button-assign").click();
    await gqlPromise;
    await expect(adminsList.locator(".q-list")).toContainText(
      "Application Administrator",
    );
    await checkA11y(page);

    gqlPromise = waitForGQLOperation(page, "UpdatePublicationAdmins");
    await adminsList.getByTestId("button_unassign").last().click();
    await gqlPromise;
  });

  await test.step("assign and remove an editor", async () => {
    let gqlPromise = waitForGQLOperation(page, "UpdatePublicationEditors");
    await userSearch(editorsList.locator("input").first(), "applicationAd");
    const items = await qSelectItems(editorsList, "input_user");
    await items.first().waitFor({ state: "visible" });
    await items.nth(0).click();
    await editorsList.getByTestId("button-assign").click();
    await gqlPromise;
    await expect(editorsList.locator(".q-list")).toContainText(
      "Application Administrator",
    );

    gqlPromise = waitForGQLOperation(page, "UpdatePublicationEditors");
    await editorsList.getByTestId("button_unassign").last().click();
    await gqlPromise;
  });
});

test("app admin: style criteria management", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", "/publication/3/setup/criteria");

  await expect(page.getByTestId("listItem")).toHaveCount(4);

  await test.step("edit existing criteria", async () => {
    await page.getByTestId("editBtn").first().click();
    await checkA11y(page, {
      exclude: ['[data-cy="description-input"'],
    });
    await page.getByTestId("name-input").click();
    await page.keyboard.press("End");
    await page.keyboard.type(" Update");
    await page
      .getByTestId("description-input")
      .locator("[contenteditable]")
      .click({ clickCount: 3 });
    await page.keyboard.type("Updated description.");
    await page.getByTestId("icon-button").click();
    const firstIconButton = page.locator(
      ".q-icon-picker__container button:first-child",
    );
    await firstIconButton.waitFor({ timeout: 10000 });
    await expect(firstIconButton.locator("i")).not.toHaveText("");
    const newIconName = await firstIconButton.locator("i").textContent();
    await firstIconButton.click();
    await page.getByTestId("button_save").click();
    await expect(page.locator('form[data-cy="listItem"]')).toHaveCount(0);
    const firstItem = page.getByTestId("listItem").first();
    await expect(firstItem).toContainText("Accessibility Update");
    await expect(firstItem).toContainText("Updated description");
    await expect(firstItem).toContainText(newIconName!);
  });

  await test.step("add a new criteria", async () => {
    await page.getByTestId("add-criteria-button").click();
    await page.getByTestId("name-input").fill("New Criteria");
    await page
      .getByTestId("description-input")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("New criteria description.");
    await page.getByTestId("button_save").click();
    await expect(page.locator('form[data-cy="listItem"]')).toHaveCount(0);
    await expect(page.getByTestId("listItem")).toHaveCount(5);
    const lastItem = page.getByTestId("listItem").last();
    await expect(lastItem).toContainText("New Criteria");
    await expect(lastItem).toContainText("New criteria description");
    await expect(lastItem).toContainText("task_alt");
  });

  await test.step("delete a criteria", async () => {
    await page.getByTestId("editBtn").first().click();
    await page.getByTestId("button-delete").click();
    await page.locator(".q-dialog .q-btn").last().click();
    await expect(page.getByTestId("listItem")).toHaveCount(4);
  });
});

test("app admin: basic settings and content", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", "/publication/3");

  await test.step("publication details page accessibility", async () => {
    await page.getByTestId("publication_details_heading").waitFor();
    await checkA11y(page);
  });

  await test.step("edit basic settings", async () => {
    await page.goto("/publication/3/setup/basic");
    await page.getByTestId("name_field").pressSequentially(" Update");
    await page
      .getByTestId("visibility_field")
      .locator("button:last-child")
      .click();

    await expect(
      page.getByTestId("allow_submissions_field").locator("button:last-child"),
    ).toHaveAttribute("aria-pressed", "false");
    await expect(
      page.getByTestId("allow_submissions_field").locator("button:first-child"),
    ).toHaveAttribute("aria-pressed", "true");

    await page
      .getByTestId("allow_submissions_field")
      .locator("button:last-child")
      .click();
    await expect(
      page.getByTestId("allow_submissions_field").locator("button:last-child"),
    ).toHaveAttribute("aria-pressed", "true");
    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");
  });

  await test.step("verify settings persistence", async () => {
    await page.goto("/publication/3/setup/basic");
    await expect(
      page.getByTestId("allow_submissions_field").locator("button:last-child"),
    ).toHaveAttribute("aria-pressed", "true");
    await checkA11y(page);
  });

  await test.step("edit content blocks", async () => {
    await page.goto("/publication/3/setup/content");
    await page.getByTestId("content_block_select").click();
    const items = await qSelectItems(page, "content_block_select");
    await items.nth(0).click();
    // Wait for the Quasar dropdown portal to close before a11y check.
    // WebKit is slow enough that the portal lingers and trips axe rules.
    await page
      .locator("[id^='q-portal--menu']")
      .waitFor({ state: "hidden" });
    await checkA11y(page, {
      exclude: ['[data-cy="content_field"'],
    });
    await page
      .getByTestId("content_field")
      .locator("[contenteditable]")
      .click();
    await page.keyboard.type("More description.");
    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");
  });
});

test("app admin: publication creation and validation", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("applicationadministrator@meshresearch.net", "/admin/publications");

  await test.step("rejects empty name", async () => {
    await page.getByTestId("create_pub_button").click();
    await page.getByTestId("new_publication_input").press("Enter");
    await expect(page.getByTestId("name_field_error")).toBeVisible();
    await expectNotification(page, "negative");
    await checkA11y(page);
  });

  await test.step("rejects name exceeding maximum length", async () => {
    await page.getByTestId("new_publication_input").fill("0".repeat(257));
    await page.getByTestId("new_publication_input").press("Enter");
    await expect(page.getByTestId("name_field_error")).toBeVisible();
    await expectNotification(page, "negative");
  });

  await test.step("creates a new publication", async () => {
    await page.getByTestId("new_publication_input").fill("Publication from Playwright");
    await page.getByTestId("new_publication_input").press("Enter");
    await expectNotification(page, "positive", "create_publication_notify");
    await expect(page).toHaveURL(/publication\/[0-9]+\/setup\/basic$/);
    await checkA11y(page);
  });

  await test.step("rejects duplicate name", async () => {
    await page.goto("/admin/publications");
    await page.getByTestId("create_pub_button").click();
    await page.getByTestId("new_publication_input").fill("Publication from Playwright");
    await page.getByTestId("new_publication_input").press("Enter");
    await expect(page.getByTestId("name_field_error")).toBeVisible();
    await expectNotification(page, "negative");
  });
});
