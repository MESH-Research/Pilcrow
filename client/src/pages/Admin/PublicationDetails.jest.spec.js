import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import PublicationDetailsPage from "./PublicationDetails.vue"
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

describe("publication details page mount", () => {
  const wrapper = mountQuasar(PublicationDetailsPage, {
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
    propsData: {
      id: "1",
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

  const publicationUsersData = [
    {
      email: "jestEditor@ccrproject.dev",
      name: "Jest Editor",
      username: "jestEditor",
      pivot: {
        id: 1,
        role_id: 3,
        user_id: 103,
      },
    },
    {
      email: "jestPublicationAdministrator@ccrproject.dev",
      name: "Jest Publication Admin",
      username: "jestPubAdmin",
      pivot: {
        id: 2,
        role_id: 2,
        user_id: 102,
      },
    },
    {
      email: "jestApplicationAdministrator@ccrproject.dev",
      name: "Jest Application Admin And Editor",
      username: "jestAppAdminEditor",
      pivot: {
        id: 3,
        role_id: 3,
        user_id: 101,
      },
    },
  ]

  test("all existing editors appear within the editors list", async () => {
    await wrapper.setData({
      publication: {
        name: "Jest Publication",
        users: publicationUsersData,
      },
    })
    const list = wrapper.findComponent({ ref: "list_assigned_editors" })
    expect(list.findAllComponents({ name: "user-list-item" })).toHaveLength(2)
  })

  test("editors can be assigned", async () => {
    await wrapper.setData({
      editor_candidate: {
        id: "104",
        name: "Jest Editor Candidate Name",
        email: "jestEditorCandidate@ccrproject.dev",
        username: "jestEditorCandidate",
      },
    })
    await wrapper.vm.assignUser("editor", wrapper.vm.editor_candidate)

    expect(mutate).toBeCalled()
    expect(notify.mock.calls[0][0].color).toBe("positive")
  })
})
