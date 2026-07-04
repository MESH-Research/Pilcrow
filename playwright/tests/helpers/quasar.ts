import type { Locator, Page } from "@playwright/test";
import { expect, test } from "@playwright/test";

/**
 * Click on a Quasar QSelect component identified by its data-cy attribute.
 * Returns the QSelect wrapper locator for further interaction.
 *
 * @param scope - A Page or Locator to search within
 * @param dataCy - The data-cy attribute value
 */
export async function qSelectOpen(
  scope: Page | Locator,
  dataCy: string,
): Promise<Locator> {
  const select = scope
    .getByTestId(dataCy)
    .locator("xpath=ancestor-or-self::*[contains(@class,'q-select')]")
    .first();
  await select.click();
  return select;
}

/**
 * Get the listbox items for a Quasar QSelect identified by data-cy.
 * The QSelect must be open (triggered by typing or clicking).
 *
 * @param scope - A Page or Locator to search within for the data-cy element
 * @param dataCy - The data-cy attribute value on or near the input
 */
export async function qSelectItems(
  scope: Page | Locator,
  dataCy: string,
): Promise<Locator> {
  let input = scope.getByTestId(dataCy);

  // If the data-cy element isn't an input, find the input inside it
  const tagName = await input.evaluate((el) => el.tagName.toLowerCase());
  if (tagName !== "input") {
    input = input.locator("input");
  }

  const id = await input.getAttribute("id");
  // The listbox is rendered at the page root, not inside the component.
  // Get the page object regardless of whether scope is a Page or Locator.
  const rootPage = "page" in input ? input.page() : (scope as Page);
  return rootPage.locator(`#${id}_lb .q-item`);
}


/**
 * Assert a Quasar notification is visible with the expected type,
 * then dismiss it by clicking the close button.
 *
 * @param page - The Playwright page
 * @param type - The notification type: "positive" (success) or "negative" (error)
 * @param dataCy - Optional data-cy attribute on the notification for more specific matching
 */
export async function expectNotification(
  page: Page,
  type: "positive" | "negative",
  dataCy?: string,
): Promise<void> {
  await test.step(`expect ${type} notification`, async () => {
    const selector = `${dataCy ? `[data-cy="${dataCy}"]` : ".q-notification"}.bg-${type}`; // eslint-disable-line playwright/no-conditional-in-test -- selector construction, not test logic
    const notification = page.locator(selector);
    await expect(notification).toBeVisible();

    // Dismiss the notification
    await notification.locator(".q-btn").first().click();
    await expect(notification).toHaveCount(0);
  });
}
