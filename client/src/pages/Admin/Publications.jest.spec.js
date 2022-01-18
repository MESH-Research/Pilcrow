import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import PublicationsPage from "./PublicationsPage.vue"
import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

const mutate = jest.fn()
const notify = jest.fn()
const query = jest.fn()

describe("publications page mount", () => {
  const wrapper = mountQuasar(PublicationsPage, {
    quasar: {
      components,
    },
    mount: {
      type: "full",
      mocks: {
        $t: (token) => token,
        $apollo: {
          query,
          mutate,
        },
      },
    },
  })

  wrapper.vm.$q.notify = notify

  beforeEach(async () => {
    mutate.mockReset()
    notify.mockReset()
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  test("all existing publications appear within the list", async () => {
    await wrapper.setData({
      publications: {
        data: [
          { id: "1", name: "Sample Jest Publication 1" },
          { id: "2", name: "Sample Jest Publication 2" },
          { id: "3", name: "Sample Jest Publication 3" },
          { id: "4", name: "Sample Jest Publication 4" },
        ],
      },
    })
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(4)
  })

  test("publications can be created", async () => {
    await wrapper.setData({
      new_publication: {
        name: "New Jest Publication Name",
      },
    })
    await wrapper.vm.createPublication()
    expect(wrapper.vm.isSubmitting).toBeFalsy()
    expect(mutate).toBeCalled()
    expect(notify.mock.calls[0][0].color).toBe("positive")
  })
})
