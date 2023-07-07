import { expect, test } from "@playwright/test"
import { defaultAxeScan, resetDb } from "../helpers"

test("login page", async ({ page, baseURL }) => {
    await resetDb(baseURL)
    await page.goto("/login")

    //Check that the page is accessible
    const { violations } = await defaultAxeScan(page).analyze()
    expect.soft(violations).toHaveLength(0)

    //Attempt login with valid credentials
    await page
        .getByTestId("email_field")
        .fill("applicationAdministrator@pilcrow.dev")
    await page.getByTestId("password_field").fill("adminPassword!@#")
    await page.getByRole("button", { name: "Login" }).click()
    await expect(page).toHaveURL("/dashboard")

    await page.getByTestId("dropdown_username").click()
    await page.getByRole("listitem").filter({ hasText: "Logout" }).click()
    await expect(page).toHaveURL("/")
    await page.goto("/login")

    //Leave the email field blank
    await page.getByTestId("email_field").press("Enter")
    await expect(
        page.locator("label", { has: page.getByTestId("email_field") })
    ).toHaveClass(/q-field--error/)
    await expect(page.getByText("Email address is required.")).toBeVisible()
    await expect(page.getByTestId("authFailureMessages")).toBeVisible()
    await expect(
        page.locator("label", { has: page.getByTestId("password_field") })
    ).toHaveClass(/q-field--error/)

    //Fill in an email and check that error message is cleared
    await page.getByTestId("email_field").fill("regularuser@pilcrow.dev")
    await expect(
        page.locator("label", { has: page.getByTestId("email_field") })
    ).not.toHaveClass(/q-field--error/)

    //Fill in the password field and check that error state is cleared
    await page.getByTestId("password_field").fill("password")
    await expect(
        page.locator("label", { has: page.getByTestId("password_field") })
    ).not.toHaveClass(/q-field--error/)

    await page.getByTestId("password_field").press("Enter")

    //Check that we're redirected to login when we try to access a protected route
    await page.goto("/account/profile")
    await expect(page).toHaveURL("/login")

    await expect(page.getByRole("alert")).toContainText(
        "login to access that page"
    )

    //Check that we're redirected to our destination after logging in
    await page.getByTestId("email_field").fill("regularuser@pilcrow.dev")
    await page.getByTestId("password_field").fill("regularPassword!@#")
    await page.getByRole("button", { name: "Login" }).click()

    await expect(page).toHaveURL("/account/profile")
})
