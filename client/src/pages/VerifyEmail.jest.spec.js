import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import VerifyEmailPage from "./VerifyEmail.vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

describe("VerifyEmailPage", () => {
  const mutate = jest.fn()
  const createWrapper = async (params, data) => {
    const $route = { params }
    const wrapper = mountQuasar(VerifyEmailPage, {
      quasar: {
        components,
      },
      mount: {
        data: () => Object.assign(VerifyEmailPage.data(), data),
        mocks: {
          $t: (token) => token,
          $apollo: {
            mutate,
          },
          $route,
        },
        stubs: ["router-link"],
      },
    })

    await wrapper.vm.$nextTick()
    return wrapper
  }

  beforeEach(() => {
    mutate.mockReset()
  })

  it("mounts without errors", async () => {
    const wrapper = await createWrapper(
      { token: "", expires: "" },
      {
        currentUser: { email_verified_at: null },
      }
    )
    expect(wrapper).toBeTruthy()
    wrapper.destroy()
  })

  test("renders success immediately if email is already verified", async () => {
    const wrapper = await createWrapper(
      { token: "", expires: "" },
      {
        currentUser: { email_verified_at: "some value" },
      }
    )
    expect(wrapper.vm.status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
    wrapper.destroy()
  })

  test("renders success", async () => {
    mutate.mockResolvedValue(true)

    const wrapper = await createWrapper(
      { token: "", expires: "" },
      { currentUser: { email_verified_at: null } }
    )

    expect(mutate).toHaveBeenCalled()
    expect(wrapper.vm.status).toBe("success")
    expect(wrapper.text()).toContain(
      "account.email_verify.verification_success"
    )
    wrapper.destroy()
  })

  it("renders errors", async () => {
    mutate.mockRejectedValue({
      graphQLErrors: [
        {
          extensions: { code: "TEST_ERROR_CODE" },
        },
      ],
    })

    const wrapper = await createWrapper(
      { token: "", expires: "" },
      { currentUser: { email_verified_at: null } }
    )

    expect(wrapper.vm.status).toBe("failure")
    const errorUl = wrapper.find("ul.errors")

    expect(errorUl.text()).toContain("TEST_ERROR_CODE")
  })
})
