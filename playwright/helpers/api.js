export async function login(page, email) {
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
    return response
}
