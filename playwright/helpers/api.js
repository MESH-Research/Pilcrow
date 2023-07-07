export async function login(page, email) {
    const URL = page.url()
    await page.goto("/")
    const response = await page.evaluate(async (email) => {
        /* eslint-env browser */
        await fetch("/sanctum/csrf-cookie", {
            credentials: "include",
        })
        const cookie = decodeURIComponent(
            document.cookie.match(/XSRF-TOKEN=([^;]+)/)[1]
        )
        return await fetch("/graphql", {
            method: "POST",
            credentials: "include",
            headers: {
                "X-XSRF-TOKEN": cookie,
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                query: `mutation {
          forceLogin(email: "${email}") {
            username, email, id, name
          }
        }`,
            }),
        })
    }, email)
    await page.goto(URL)
    return response
}
