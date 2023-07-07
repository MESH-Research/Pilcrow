import axiosImport from "axios"
import https from "https"

const axios = axiosImport.create({
    httpsAgent: new https.Agent({
        rejectUnauthorized: false,
    }),
})

export function resetDb(baseURL) {
    return new Promise((resolve, reject) => {
        axios({
            url: `${baseURL}graphql`,
            method: "POST",
            data: {
                query: "mutation { resetDb }",
            },
        }).then((response) => {
            const {
                data: { data, errors },
            } = response
            if (!data) {
                console.log(response)
            }
            if (errors) {
                console.log(errors)
                reject()
            }
            resolve(data.artisanCommand)
        })
    })
}

export function setupResetDb(baseURL) {
    return new Promise((resolve, reject) => {
        axios({
            url: `${baseURL}graphql`,
            method: "POST",
            data: {
                query: "mutation { setupResetDb }",
            },
        }).then((response) => {
            const {
                data: { data, errors },
            } = response
            if (!data) {
                console.log(response)
            }
            if (errors) {
                console.log(errors)
                reject()
            }
            resolve(data.artisanCommand)
        })
    })
}
