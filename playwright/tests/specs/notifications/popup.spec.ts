import { test, expect } from "@fixtures/base";
import { waitForGQLOperation } from "@helpers/graphql";

test("regular user: notification management", async ({
  page,
  resetDatabase,
  seedSubmission,
  loginAs,
}) => {
  await resetDatabase();
  const subId = await seedSubmission({ status: "INITIALLY_SUBMITTED" });

  await test.step("generate a notification via status change", async () => {
    await loginAs("applicationadministrator@meshresearch.net", `/submission/${subId}/review`);
    await page.getByTestId("status-dropdown").waitFor();
    await page.getByTestId("status-dropdown").click();
    await page.getByTestId("open_for_review").click();
    await Promise.all([
      page.getByTestId("dirtyYesChangeStatus").click(),
      waitForGQLOperation(page, "UpdateSubmissionStatus"),
    ]);
  });

  await test.step("mark individual notification as read", async () => {
    await loginAs("regularuser@meshresearch.net", "/dashboard");
    await page.getByTestId("dropdown_notificiations").click();
    await expect(page.getByTestId("notification_list_item").nth(0)).toBeVisible();
    await expect(
      page.getByTestId("notification_list_item").nth(0),
    ).toHaveClass(/unread/);
    await page.getByTestId("notification_list_item").nth(0).click();
    await expect(
      page.getByTestId("notification_list_item").nth(0),
    ).not.toHaveClass(/unread/);
  });

  await test.step("mark all notifications as read", async () => {
    await page.goto("/dashboard");
    await page.getByTestId("dropdown_notificiations").click();
    await expect(page.getByTestId("notification_list_item").nth(0)).toBeVisible();
    await Promise.all([
      page.getByTestId("dismiss_all_notifications").click(),
      waitForGQLOperation(page, "MarkAllNotificationsRead"),
    ]);
    await expect(
      page.getByTestId("notification_list_item").nth(0),
    ).not.toHaveClass(/unread/);
  });
});
