describe("Copywriting Screenshots", () => {
    it("Makes screenshots", () => {
        function setLocale(locale = 'copy') {
            cy.get('#locale-switch').then((el) => {
                el.val(locale)
                const event = new Event('input', { bubbles: true })
                el.get(0).dispatchEvent(event)
            })
        }
        function screenshotPath(url, waitOn) {
            cy.visit(url)
            cy.task("log", `Creating screenshots for: ${url}`)
            if (waitOn) cy.get(waitOn)
            const name = url.replace(/\//g, "#");

            setLocale('en-US')
            cy.screenshot(name + ' (Copy)')

            setLocale('copy')
            cy.screenshot(name + ' (Keys)')
        }
        cy.task("resetDb")
        cy.login({ email: "applicationadministrator@ccrproject.dev" })
        screenshotPath('/')
        screenshotPath('/login')
        screenshotPath('/register')
        screenshotPath('/dashboard')
        screenshotPath("/publications", "[data-cy=publications_list]")
        screenshotPath("account/profile", "[data-cy=update_user_email]")
        screenshotPath("account/metadata", "[data-cy=professional_title]")
        screenshotPath("/publication/1/setup/basic", "[data-cy=visibility_field]")
        screenshotPath("/publication/1/setup/users", "[data-cy=editors_list]")
        screenshotPath("/publication/1/setup/content", "[data-cy=content_block_select]")
        screenshotPath("/publication/1/setup/criteria", "[data-cy=add-criteria-button]")
        screenshotPath("/feed")
        screenshotPath("/admin/users", "[data-cy=user_list_pagination]")
        screenshotPath("/admin/user/1", "[data-cy=role_item]")
        screenshotPath("/admin/publications", "[data-cy=publications_list]")
        screenshotPath("/publication/1", "[data-cy=publication_home_content]")
        screenshotPath("/submissions", "[data-cy=submissions_list]")
        screenshotPath("/submission/100", "[data-cy=reviewers_list]")
        screenshotPath("/submission/review/100", "[data-cy=submission_review_layout]")
    })
})
