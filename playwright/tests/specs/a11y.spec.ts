import { test } from "@fixtures/base";
import { checkA11y } from "@helpers/a11y";

test("guest: home page accessibility", async ({ page }) => {
  await page.goto("/");
  await page.getByTestId("vueIndex").waitFor();
  await checkA11y(page);
});

test("regular user: dashboard accessibility", async ({
  page,
  resetDatabase,
  loginAs,
}) => {
  await resetDatabase();
  await loginAs("regularuser@meshresearch.net", "/dashboard");
  await page.getByTestId("vueDashboard").waitFor();
  await checkA11y(page);
});
