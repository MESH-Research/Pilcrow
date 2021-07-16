import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import LoginPage from "./Login.vue"

import {
  QIcon,
  QCardSection,
  QInput,
  QCard,
  QCardActions,
  QBtn,
  QForm,
  QPage,
  QBanner,
} from "quasar"
describe("LoginPage", () => {
  const mutate = jest.fn()
  const sessionStorage = jest.fn()
  const wrapper = mountQuasar(LoginPage, {
    quasar: {
      components: {
        QIcon,
        QCardSection,
        QInput,
        QCard,
        QCardActions,
        QBtn,
        QForm,
        QPage,
        QBanner,
      },
    },
    mount: {
      type: "shallow",
      mocks: {
        $t: (token) => token,
        $apollo: {
          mutate,
        },
        $q: {
          sessionStorage: {
            remove: sessionStorage,
            getItem: sessionStorage,
          },
        },
      },
      stubs: ["router-link"],
    },
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  test("login action attempts mutation", async () => {
    mutate.mockClear()

    await wrapper.setData({
      form: {
        email: "test@gmail.com",
        password: "blahblahblah",
      },
    })

    await wrapper.vm.login()
    expect(mutate).toBeCalled()
  })

  test("login field email is required", async () => {
    mutate.mockClear()

    await wrapper.setData({
      form: {
        email: "",
      },
    })

    await wrapper.vm.login()
    expect(mutate).not.toBeCalled()
    expect(wrapper.vm.$v.form.email.$invalid).toBe(true)

    await wrapper.setData({
      form: {
        email: "notanemail",
      },
    })

    await wrapper.vm.login()
    expect(mutate).not.toBeCalled()
    expect(wrapper.vm.$v.form.email.$invalid).toBe(true)
  })

  test("password field is required", async () => {
    mutate.mockClear()
    await wrapper.setData({
      form: {
        password: "",
      },
    })

    await wrapper.vm.login()
    expect(mutate).not.toBeCalled()
    expect(wrapper.vm.$v.form.email.$invalid).toBe(true)
  })

  test("login action extracts auth errors", async () => {
    mutate.mockClear()
    await wrapper.setData({
      form: {
        email: "my@email.com",
        password: "mysecurepassword",
      },
    })
    const error = {
      graphQLErrors: [
        {
          extensions: {
            code: "SOME_ERROR_CODE",
          },
        },
      ],
    }
    mutate.mockRejectedValue(error)
    await wrapper.vm.login()

    expect(mutate).toBeCalled()
    expect(wrapper.vm.error).toEqual("SOME_ERROR_CODE")
  })
})
