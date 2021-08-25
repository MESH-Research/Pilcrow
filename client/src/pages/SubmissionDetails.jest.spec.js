import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionDetailsPage from "./SubmissionDetails.vue"
import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

const query = jest.fn()

describe("submissions details page mount", () => {
  const wrapper = mountQuasar(SubmissionDetailsPage, {
    quasar: {
      components,
    },
    mount: {
      type: "shallow",
      mocks: {
        $t: (token) => token,
        $apollo: {
          query,
        },
      },
    },
    propsData: {
      id: "1",
    },
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  test("all assigned reviwers appear within the assigned reviewers list", async () => {
    await wrapper.setData({
      submission: {
        users: [
          {
            name: "Jest Submitter 1",
            username: "jestSubmitter1",
            email: "jestsubmitter1@msu.edu",
            pivot: {
              id: "1",
              role_id: "6",
            },
          },
          {
            name: "Jest Reviewer 1",
            username: "jestReviewer1",
            email: "jestreviewer1@msu.edu",
            pivot: {
              id: "2",
              role_id: "5",
            },
          },
          {
            name: "Jest Reviewer 2",
            username: "jestReviewer2",
            email: "jestreviewer2@msu.edu",
            pivot: {
              id: "3",
              role_id: "5",
            },
          },
          {
            name: "Jest Reviewer 3",
            username: "jestReviewer3",
            email: "jestreviewer3@msu.edu",
            pivot: {
              id: "4",
              role_id: "5",
            },
          },
        ],
      },
    })

    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(3)
  })

  test("a default message still appears when there are no assigned reviewers", async () => {
    await wrapper.setData({
      submission: {
        users: [],
      },
    })
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(1)
  })
})
