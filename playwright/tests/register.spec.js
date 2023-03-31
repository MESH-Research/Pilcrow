import { expect, test } from "@playwright/test"
import { defaultAxeScan, getInputLabel, resetDb } from "../helpers"

test("test", async ({ page, baseURL }) => {
    await resetDb(baseURL)

    await page.goto("/register")

    //Check accessibility
    const { violations } = await defaultAxeScan(page).analyze()

    expect(violations).toHaveLength(0)

    //Check required fields
    await page.getByTestId("email_field").press("Enter")
    const requiredFields = ["email_field", "password_field", "username_field"]
    requiredFields.forEach(async (id) => {
        await expect(getInputLabel(page.getByTestId(id))).toHaveClass(
            /q-field--error/
        )
    })

    await expect(page.getByText("Email address is required.")).toBeVisible()

    const emailField = page.getByTestId("email_field")
    const emailLabel = getInputLabel(emailField)

    //Email must be a valid email
    await emailField.fill("pilcrowproject")
    await expect(emailLabel).toHaveClass(/q-field--error/)
    await expect(page.getByText("enter a valid email")).toBeVisible()

    //Full email should be valid
    await emailField.fill("pilcrow@pilcrow.dev")
    await expect(emailLabel).not.toHaveClass(/q-field--error/)

    const usernameField = page.getByTestId("username_field")
    const usernameLabel = getInputLabel(usernameField)

    //Valid user name
    await usernameField.fill("newUser")
    await expect(usernameLabel).not.toHaveClass(/q-field--error/)

    const passwordField = page.getByTestId("password_field")
    const passwordLabel = getInputLabel(passwordField)

    //Password must be complex
    await passwordField.fill("password")

    await expect(passwordLabel).toHaveClass(/q-field--error/)
    await expect(page.getByText("be more complex")).toBeVisible()

    //Password is complex
    await passwordField.fill("!@#$#@passwor12")
    await expect(passwordLabel).not.toHaveClass(/q-field--error/)

    //Email address must be unique
    await emailField.fill("regularuser@pilcrow.dev")
    await emailField.press("Enter")
    await expect(emailLabel).toHaveClass(/q-field--error/)
    await expect(page.getByText("email is already registered")).toBeVisible()

    await emailField.fill("newUser@pilcrow.dev")

    //username must be unique
    await usernameField.fill("regularUser")
    await usernameField.press("Enter")

    await expect(usernameLabel).toHaveClass(/q-field--error/)
    await expect(page.getByText("username is not available")).toBeVisible()

    //Sucessful registration
    await usernameField.fill("newUser")

    await page.getByRole("button", { name: "Create Account" }).click()
    await expect(page).toHaveURL("/dashboard")
})
