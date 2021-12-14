import RegisterPage from "./Register.vue"
import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import { CREATE_USER, LOGIN } from "src/graphql/mutations"
import { createMockClient } from "mock-apollo-client"
import { DefaultApolloClient } from "@vue/apollo-composable"
import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val && val.component && val.component.name != null) {
    object[key] = val
  }
  return object
}, {})

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  SessionStorage: {
    remove: jest.fn(),
    getItem: jest.fn(),
  },
}))

describe("RegisterPage", () => {
  const wrapperFactory = (mocks = []) => {
    const apolloProvider = {}
    const mockClient = createMockClient({ query: { fetchPolicy: "no-cache" } })
    apolloProvider[DefaultApolloClient] = mockClient

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    })

    return {
      wrapper: mountQuasar(RegisterPage, {
        quasar: {
          components,
        },
        mount: {
          provide: apolloProvider,
          stubs: ["router-link"],
          mocks: {
            $t: (token) => token,
          },
        },
      }),
      mockClient,
    }
  }

  it("mounts without errors", () => {
    expect(wrapperFactory().wrapper).toBeTruthy()
  })

  test("form submits on valid data", async () => {
    const { wrapper, mockClient } = wrapperFactory()

    const user = {
      id: 1,
      name: "Joe Doe",
      username: "user",
      email_verified_at: null,
      password: "albancub4Grac&",
      email: "test@example.com",
      created_at: "2021-12-01",
      roles: [],
    }

    const mutateHandler = jest
      .fn()
      .mockResolvedValue({ data: { createUser: { ...user } } })

    mockClient.setRequestHandler(CREATE_USER, mutateHandler)

    mockClient.setRequestHandler(
      LOGIN,
      jest.fn().mockResolvedValue({ data: { login: { ...user } } })
    )
    Object.assign(wrapper.vm.user, { ...user })

    await wrapper.vm.handleSubmit()
    expect(wrapper.vm.formErrorMsg.value).toBeFalsy()
    expect(mutateHandler).toBeCalled()
  })
})
