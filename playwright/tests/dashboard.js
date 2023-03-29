import { expect, test } from '@playwright/test';
import { resetDb } from '../helpers';

test.use({ storageState: '.auth/regularUser.json' })

test('Notification popup', async ({ browser, baseURL }) => {
  await resetDb(baseURL)
  const regularContext = await browser.newContext({ storageState: '.auth/regularUser.json' });
  const regularPage = await regularContext.newPage();
  await regularPage.goto('/dashboard');
  await regularPage.getByTestId('dropdown_notificiations').click();
  await expect(regularPage.getByTestId('notification_list_item').first()).toHaveClass(/unread/);
  await regularPage.getByTestId('notification_list_item').first().click();
  await expect(regularPage.getByTestId('notification_list_item').first()).not.toHaveClass(/unread/)

  const adminContext = await browser.newContext({ storageState: '.auth/applicationAdmin.json' });
  const adminPage = await adminContext.newPage();

  await adminPage.goto('/submission/review/108');
  await adminPage.getByTestId('open_for_review').click();
  await adminPage.getByTestId('dirtyYesChangeStatus').click();
  await expect(adminPage.getByRole('alert')).toHaveClass(/bg-positive/)

  await regularPage.goto('/')
  await regularPage.getByTestId('dropdown_notificiations').click();
  await expect(regularPage.getByTestId('notification_list_item').first()).toContainText('currently under review.')
  await regularPage.getByTestId('dismiss_all_notifications').click();

  for (const item of await regularPage.getByTestId('notification_list_item').all()) {
    await expect(item).not.toHaveClass(/unread/)
  }

  await adminContext.close();
  await regularContext.close();

});