describe("Copywriting Screenshots", () => {
    it("Makes screenshots", () => {
        function setLocale(locale = 'copy') {
            cy.get('#locale-switch').then((el) => {
                el.get(0).__vueParentComponent.exposed.locale.value = locale
            })
        }
        function screenshotPath(url) {
            cy.visit(url)
            const name = url.replace(/\//g, "#");
            cy.task("log", name)
            setLocale('en-US')
            cy.screenshot(name + ' (Copy)')

            setLocale('copy')
            cy.screenshot(name + ' (Keys)')
        }

        cy.login({ email: "applicationadministrator@ccrproject.dev" })
        screenshotPath('/')
        screenshotPath('/login')
        screenshotPath('/register')
        screenshotPath('/dashboard')
        screenshotPath("/publications")
        screenshotPath("account/profile")
        screenshotPath("account/metadata")
        screenshotPath("/publication/1/setup/basic")
        screenshotPath("/publication/1/setup/users")
        screenshotPath("/publication/1/setup/content")
        screenshotPath("/publication/1/setup/criteria")
        screenshotPath("/feed")
        screenshotPath("/admin/users")
        screenshotPath("/admin/user/1")
        screenshotPath("/admin/publications")
        screenshotPath("/publication/1")
        screenshotPath("/submissions")
        screenshotPath("/submission/1")
        screenshotPath("/submission/review/1")
    })
})