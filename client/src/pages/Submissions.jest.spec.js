import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionsPage from "./SubmissionPage.vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

const query = jest.fn()
const notify = jest.fn()

describe("submissions page mount", () => {
  const wrapper = mountQuasar(SubmissionsPage, {
    quasar: {
      components,
    },
    mount: {
      type: "full",
      stubs: ["router-link"],
      mocks: {
        $t: (token) => token,
        $apollo: {
          query,
        },
      },
    },
  })

  wrapper.vm.$q.notify = notify

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  test("all existing submissions appear within the list", async () => {
    await wrapper.setData({
      submissions: {
        data: [
          {
            id: "1",
            title: "Jest Submission 1",
            publication: { id: "1", name: "Jest Publication" },
            files: [],
          },
          {
            id: "2",
            title: "Jest Submission 2",
            publication: { id: "1", name: "Jest Publication" },
            files: [],
          },
          {
            id: "3",
            title: "Jest Submission 3",
            publication: { id: "1", name: "Jest Publication" },
            files: [],
          },
          {
            id: "4",
            title: "Jest Submission 4",
            publication: { id: "1", name: "Jest Publication" },
            files: [],
          },
          {
            id: "5",
            title: "Jest Submission 5",
            publication: { id: "1", name: "Jest Publication" },
            files: [],
          },
        ],
      },
    })
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(5)
  })
})
