import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";

test("regular user: profile", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/account/profile");

  await test.step("page is accessible", async () => {
    await page.getByTestId("page_heading").waitFor();
    await checkA11y(page);
  });

  await test.step("update name and username", async () => {
    await page.getByTestId("update_user_name").fill("Updated User");
    await page.getByTestId("update_user_username").clear();
    await page.getByTestId("update_user_username").fill("updatedUser");
    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");
  });

  await test.step("update position title", async () => {
    const value = "Updated position Title";
    await page.getByTestId("position_title").clear();
    await page.getByTestId("position_title").fill(value);
    await expect(page.getByTestId("button_save")).toBeVisible();
    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");
  });

  await test.step("update facebook username", async () => {
    const value = "facebookusername";
    await page.getByTestId("facebook").fill(value);
    await expect(page.getByTestId("button_save")).toBeVisible();
    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");
  });

  await test.step("add and reorder websites", async () => {
    const site1 = "https://pilcrow.lndo.site";
    const site2 = "https://yahoo.com";
    const list = page.getByTestId("websites_list_control");

    const inputField = list.getByTestId("input_field");
    await inputField.click();
    await inputField.fill(site1);
    await inputField.press("Enter");
    await inputField.fill(site2);
    await inputField.press("Enter");
    await list.getByTestId("arrow_upward_1").waitFor();
    await list.getByTestId("arrow_upward_1").click();

    await page.getByTestId("button_save").click();
    await expect(page.getByTestId("button_saved")).toContainText("Saved");

    await list.getByTestId("edit_btn_0").click();
    await expect(list.getByTestId("edit_input_0")).toHaveValue(site2);
  });
});

test("regular user: settings", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("settingsuser@meshresearch.net", "/account/settings");
  await page.getByTestId("page_heading").waitFor();

  await test.step("page is accessible", async () => {
    await checkA11y(page);
  });

  await test.step("update email", async () => {
    await page.getByTestId("update_user_email").clear();
    await page.getByTestId("update_user_email").fill("updateduser@meshresearch.net");
    await page.getByTestId("button_save").click();
    const notify = page.getByTestId("update_user_notify");
    await expect(notify).toBeVisible();
    await expect(notify).toHaveClass(/bg-positive/);
  });

  await test.step("update password", async () => {
    await page.getByTestId("update_user_password").clear();
    await page.getByTestId("update_user_password").fill("XMYeygtC7TuxgER4");
    await page.getByTestId("button_save").click();
    const notify = page.getByTestId("update_user_notify");
    await expect(notify).toBeVisible();
    await expect(notify).toHaveClass(/bg-positive/);
  });
});
