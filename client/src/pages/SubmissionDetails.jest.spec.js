import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionDetailsPage from "./SubmissionDetails.vue"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import flushPromises from "flush-promises"
import { GET_SUBMISSION } from "src/graphql/queries"
import { InMemoryCache } from "@apollo/client/core"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (t) => t,
    te: () => true,
  }),
}))

installQuasarPlugin()

describe("submissions details page mount", () => {
  const cache = new InMemoryCache({
    addTypename: true,
  })
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
    cache,
  })
  const makeWrapper = async () => {
    const wrapper = mount(SubmissionDetailsPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
      },
      props: {
        id: "1",
      },
    })
    await flushPromises()
    return wrapper
  }

  const submissionUsersData = {
    submitters: [
      {
        id: "1",
        name: "Jest Submitter 1",
        username: "jestSubmitter1",
        email: "jestsubmitter1@msu.edu",
      },
      {
        id: "5",
        name: "Jest Submitter 2",
        username: "jestSubmitter2",
        email: "jestsubmitter2@msu.edu",
      },
    ],
    reviewers: [
      {
        id: "2",
        name: "Jest Reviewer 1",
        username: "jestReviewer1",
        email: "jestreviewer1@msu.edu",
      },
      {
        id: "3",
        name: "Jest Reviewer 2",
        username: "jestReviewer2",
        email: "jestreviewer2@msu.edu",
      },
      {
        id: "4",
        name: "Jest Reviewer 3 and Review Coordinator 1",
        username: "jestReviewer3Coordinator1",
        email: "jestreviewer3@msu.edu",
      },
    ],
    review_coordinators: [
      {
        id: "4",
        name: "Jest Reviewer 3 and Review Coordinator 1",
        username: "jestReviewer3Coordinator1",
        email: "jestreviewer3@msu.edu",
      },
    ],
  }

  const GetSubHandler = jest.fn()
  mockClient.setRequestHandler(GET_SUBMISSION, GetSubHandler)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  const defaultApolloMock = () => {
    GetSubHandler.mockResolvedValue({
      data: {
        submission: {
          id: 1,
          status: 0,
          __typename: "Submission",
          title: "This Submission",
          publication: {
            id: 1,
            name: "Jest Publication",
            style_criterias: [],
          },
          ...submissionUsersData,
        },
      },
    })
  }

  it("mounts without errors", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    expect(GetSubHandler).toBeCalledWith({ id: "1" })
    expect(wrapper).toBeTruthy()
  })

  test("all assigned submitters appear within the assigned submitters list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.find("[data-cy=submitters_list]")
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.find("[data-cy=reviewers_list]")
    expect(list.findAll(".q-item")).toHaveLength(3)
  })

  test("all assigned review coordinators appear within the assigned review coordinators list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    const list = wrapper.find('[data-cy="coordinators_list"]')
    expect(list.findAll(".q-item")).toHaveLength(1)
  })
})
