import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";

test("guest: login page", async ({ page, resetDatabase }) => {
  await resetDatabase();
  await page.goto("/login");

  await test.step("page is accessible", async () => {
    await page.getByTestId("password_field").waitFor();
    await checkA11y(page);
  });

  await test.step("validates fields and displays errors", async () => {
    const form = page.locator(".q-form");

    await form.getByTestId("email_field").press("Enter");
    await expect(
      form.getByTestId("email_field").locator("xpath=ancestor::label"),
    ).toHaveClass(/q-field--error/);

    await form.getByTestId("email_field").fill("regularuser@meshresearch.net");
    await form.getByTestId("email_field").press("Enter");
    const passwordLabel = form
      .getByTestId("password_field")
      .locator("xpath=ancestor::label");
    await expect(passwordLabel).toHaveClass(/q-field--error/);
    await passwordLabel.locator("input").fill("somePass");
    await passwordLabel.locator("input").press("Enter");

    await expect(page.getByTestId("authFailureMessages")).toBeVisible();
    await expect(page.getByTestId("authFailureMessages")).toContainText(
      "combination is incorrect",
    );
    await checkA11y(page);
  });

  await test.step("redirects to login for protected pages", async () => {
    await page.goto("/account/profile");
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('[role="alert"]')).toContainText(
      "log in to access that page",
    );

    const form = page.locator(".q-form");
    await form.getByTestId("email_field").fill("regularuser@meshresearch.net");
    await form.getByTestId("password_field").fill("regularPassword!@#");
    await form.getByTestId("password_field").press("Enter");
    await expect(page).toHaveURL(/\/account\/profile/);
    await checkA11y(page);
  });

  await test.step("allows a user to login", async () => {
    await page.goto("/login");
    const form = page.locator(".q-form");
    await form.getByTestId("email_field").fill("regularuser@meshresearch.net");
    await form.getByTestId("password_field").fill("regularPassword!@#");
    await form.locator(".q-card__actions").getByText("Log In").click();
    await expect(page).toHaveURL(/\/dashboard/);
  });
});

test("guest: registration", async ({ page, resetDatabase }) => {
  await resetDatabase();
  await page.goto("/register");

  await test.step("page is accessible", async () => {
    await page.getByTestId("password_field").waitFor();
    await checkA11y(page);
  });

  const form = page.locator(".q-form");
  const emailLabel = form
    .getByTestId("email_field")
    .locator("xpath=ancestor::label");
  const usernameLabel = form
    .getByTestId("username_field")
    .locator("xpath=ancestor::label");
  const passwordLabel = form
    .getByTestId("password_field")
    .locator("xpath=ancestor::label");

  await test.step("email is required", async () => {
    await form.getByTestId("email_field").press("Enter");
    await expect(emailLabel).toHaveClass(/q-field--error/);
  });

  await test.step("email must be valid", async () => {
    await form.getByTestId("email_field").fill("pilcrowproject");
    await form.getByTestId("email_field").press("Enter");
    await expect(emailLabel).toContainText("a valid email");
  });

  await test.step("valid email is accepted", async () => {
    await form.getByTestId("email_field").fill("pilcrowproject@meshresearch.net");
    await form.getByTestId("email_field").press("Enter");
    await expect(emailLabel).not.toHaveClass(/q-field--error/);
  });

  await test.step("username is required", async () => {
    await expect(usernameLabel).toHaveClass(/q-field--error/);
    await expect(usernameLabel).toContainText("is required");
  });

  await test.step("valid username is accepted", async () => {
    await form.getByTestId("username_field").fill("newUser");
    await form.getByTestId("username_field").press("Enter");
    await expect(usernameLabel).not.toHaveClass(/q-field--error/);
  });

  await test.step("password is required", async () => {
    await expect(passwordLabel).toHaveClass(/q-field--error/);
  });

  await test.step("password must be complex", async () => {
    await form.getByTestId("password_field").fill("password");
    await expect(passwordLabel).toContainText("be more complex");
  });

  await test.step("complex password is accepted", async () => {
    await form.getByTestId("password_field").fill("password!@#$#@password");
    await expect(passwordLabel).not.toHaveClass(/q-field--error/);
  });

  await test.step("username must be unique", async () => {
    await form.getByTestId("username_field").fill("regularUser");
    await form.getByTestId("username_field").press("Enter");
    await expect(usernameLabel).toContainText("is not available");
  });

  await test.step("email must be unique", async () => {
    await form.getByTestId("username_field").fill("regularUser123");
    await form.getByTestId("email_field").fill("regularuser@meshresearch.net");
    await form.getByTestId("email_field").press("Enter");
    await expect(emailLabel).toContainText("already registered");
  });

  await test.step("successful registration", async () => {
    const form = page.locator(".q-form");
    await form.getByTestId("username_field").fill("brandNewUser");
    await form.getByTestId("email_field").fill("newvalidemail@meshresearch.net");
    await form.getByTestId("password_field").fill("password!@#$#@password");
    await form.locator('[type="submit"]').click();
    await expect(page).toHaveURL(/\/dashboard/);
    await checkA11y(page);
  });
});

test("regular user: header and logout", async ({
  page,
  resetDatabase,
  loginAs,
  baseURL,
}) => {
  await resetDatabase();

  await test.step("unauthenticated header shows login and register links", async () => {
    await page.goto("/");
    const header = page.locator("header");
    await expect(header.getByRole("link", { name: "Login" })).toHaveAttribute(
      "href",
      "/login",
    );
    await expect(
      header.getByRole("link", { name: "Register" }),
    ).toHaveAttribute("href", "/register");
  });

  await test.step("authenticated header shows user menu", async () => {
    await loginAs("regularuser@meshresearch.net", "/", { noCache: true });
    const header = page.locator("header");

    await header.getByText("regularUser").click();
    await expect(page.getByTestId("link_my_account")).toHaveAttribute(
      "href",
      "/account/settings",
    );
  });

  await test.step("logout returns to unauthenticated state", async () => {
    const header = page.locator("header");
    await page.getByTestId("headerUserMenu").getByText("Logout").click();

    await expect(header.getByText("Login")).toBeVisible();
    await expect(header.getByText("Register")).toBeVisible();

    await page.goto("/");
    await expect(header.getByText("Login")).toBeVisible();
    await expect(header.getByText("Register")).toBeVisible();
  });

  await test.step("dirty guard prevents logout with unsaved changes", async () => {
    await loginAs("regularuser@meshresearch.net", "/account/profile", {
      noCache: true,
    });
    await page.getByTestId("facebook").fill("myface");

    await page.locator("header").getByText("regularUser").click();
    await page.getByTestId("headerUserMenu").getByText("Logout").click();
    await page.getByTestId("dirtyKeepChanges").click();
    await expect(page).toHaveURL(/\/account\/profile/);

    await page.getByTestId("headerUserMenu").getByText("Logout").click();
    await page.getByTestId("dirtyDiscardChanges").click();
    await expect(page).toHaveURL(baseURL + "/");
    await expect(page.locator("header").getByText("Login")).toBeVisible();
  });
});
