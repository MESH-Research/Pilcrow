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
      type: "full",
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

  const submissionUsersData = [
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
      name: "Jest Reviewer 3 and Review Coordinator 1",
      username: "jestReviewer3Coordinator1",
      email: "jestreviewer3@msu.edu",
      pivot: {
        id: "4",
        role_id: "5",
      },
    },
    {
      name: "Jest Reviewer 3 and Review Coordinator 1",
      username: "jestReviewer3Coordinator1",
      email: "jestreviewer3@msu.edu",
      pivot: {
        id: "4",
        role_id: "3",
      },
    },
    {
      name: "Jest Submitter 2",
      username: "jestSubmitter2",
      email: "jestsubmitter2@msu.edu",
      pivot: {
        id: "5",
        role_id: "6",
      },
    },
  ]

  test("all assigned submitters appear within the assigned submitters list", async () => {
    await wrapper.setData({
      submission: {
        users: submissionUsersData,
      },
    })
    const list = wrapper.findComponent({ ref: "list_assigned_submitters" })
    expect(list.findAllComponents({ name: "user-list-item" })).toHaveLength(2)
  })

  test("an error message appears when there are no assigned submitters", async () => {
    await wrapper.setData({
      submission: {
        users: [],
      },
    })
    const list = wrapper.findComponent({ ref: "list_no_submitters" })
    expect(list.text()).toContain("submissions.submitter.none")
    expect(list.findAllComponents({ name: "q-card" })).toHaveLength(1)
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    await wrapper.setData({
      submission: {
        users: submissionUsersData,
      },
    })
    const list = wrapper.findComponent({ ref: "list_assigned_reviewers" })
    expect(list.findAllComponents({ name: "q-card" })).toHaveLength(3)
  })

  test("a default message still appears when there are no assigned reviewers", async () => {
    await wrapper.setData({
      submission: {
        users: [],
      },
    })
    const list = wrapper.findComponent({ ref: "list_no_reviewers" })
    expect(list.text()).toContain("submissions.reviewer.none")
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(1)
  })
})
