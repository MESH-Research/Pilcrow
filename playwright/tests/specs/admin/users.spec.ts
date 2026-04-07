import { test, expect } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";

test("app admin: access control and page accessibility", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();

  await test.step("regular users are denied access", async () => {
    await loginAs("regularuser@meshresearch.net", "/dashboard");
    await page.goto("/admin/users");
    await expect(page).toHaveURL(/\/error403/);
  });

  await test.step("admins can access the users list", async () => {
    await loginAs("applicationadministrator@meshresearch.net", "/admin/users");
    await expect(page).not.toHaveURL(/\/error403/);
    await page.getByTestId("userListBasicItem").first().waitFor();
    await checkA11y(page);
  });

  await test.step("admin user details page is accessible", async () => {
    await page.goto("/admin/user/2");
    await page.getByTestId("userDetailsHeading").waitFor();
    await checkA11y(page);
  });

  await test.step("non-admin user details page is accessible", async () => {
    await page.goto("/admin/user/1");
    await page.getByTestId("userDetailsHeading").waitFor();
    await checkA11y(page);
  });
});
