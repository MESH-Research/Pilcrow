import { expect, test } from "@playwright/test"
import { login, resetDb } from "../helpers"

test("Make copyrighting screenshots", async ({ page, baseURL }) => {
    async function setLocale(locale = "copy") {
        await page.locator("#locale-switch").evaluate((el, locale) => {
            el.value = locale
            const event = new Event("input", { bubbles: true })
            el.dispatchEvent(event)
        }, locale)
    }
    async function screenshotPath(url, waitOn) {
        await page.goto(url)
        //eslint-disable-next-line
    if (waitOn) await expect.soft(page.locator(waitOn)).toBeVisible()

        const name = url.replace(/\//g, "#")

        await setLocale("en-US")
        await page.screenshot({
            path: "screenshots/" + name + " (Copy).png",
            fullPage: true,
        })

        setLocale("copy")
        await page.screenshot({
            path: "screenshots/" + name + " (Keys).png",
            fullPage: true,
        })
    }
    await resetDb(baseURL)
    await login(page, "applicationadministrator@pilcrow.dev")
    await screenshotPath("/")
    await screenshotPath("/login")
    await screenshotPath("/register")
    await screenshotPath("/dashboard")
    await screenshotPath("/publications", "[data-cy=publications_list]")
    await screenshotPath("account/profile", "[data-cy=update_user_email]")
    await screenshotPath("account/metadata", "[data-cy=professional_title]")
    await screenshotPath(
        "/publication/1/setup/basic",
        "[data-cy=visibility_field]"
    )
    await screenshotPath("/publication/1/setup/users", "[data-cy=editors_list]")
    await screenshotPath(
        "/publication/1/setup/content",
        "[data-cy=content_block_select]"
    )
    await screenshotPath(
        "/publication/1/setup/criteria",
        "[data-cy=add-criteria-button]"
    )
    await screenshotPath("/feed")
    await screenshotPath("/admin/users", "[data-cy=user_list_pagination]")
    await screenshotPath("/admin/user/1", "[data-cy=role_item]")
    await screenshotPath("/admin/publications", "[data-cy=publications_list]")
    await screenshotPath("/publication/1", "[data-cy=publication_home_content]")
    await screenshotPath("/submissions", "[data-cy=submissions_table]")
    await screenshotPath("/submission/100/details", "[data-cy=reviewers_list]")
    await screenshotPath("/submission/100/details", "[data-cy=reviewers_list]")
    await screenshotPath(
        "/submission/100/review",
        "[data-cy=submission_review_layout]"
    )
    await screenshotPath(
        "/submission/100/review",
        "[data-cy=submission_review_layout]"
    )
    await screenshotPath(
        "/publication/1/create",
        "[data-cy=submission_create_subheading]"
    )
    await login(page, "regularuser@pilcrow.dev")

    await screenshotPath("/submission/104/draft", "[data-cy=submission_title]")
    await screenshotPath(
        "/submission/104/content",
        "[data-cy=submission_content_title]"
    )
})
