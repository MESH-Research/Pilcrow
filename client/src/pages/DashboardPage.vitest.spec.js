import DashboardPage from "./DashboardPage.vue"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { afterEach, describe, expect, it, test, vi } from "vitest"
import { installApolloClient } from "test/vitest/utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { ref } from "vue"
import { useCurrentUser } from "src/use/user"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Dashboard Page", () => {
  const CurrentUserSubmissions = vi.fn()
  mockClient.setRequestHandler(CURRENT_USER_SUBMISSIONS, CurrentUserSubmissions)

  afterEach(() => {
    CurrentUserSubmissions.mockClear()
  })

  const wrapperFactory = async () => {
    const wrapper = mount(DashboardPage, {
      global: {
        stubs: ["router-link", "i18n-t"],
      },
    })
    await flushPromises()
    return wrapper
  }

  function mockSubmission(id, status, role) {
    const submission_my_role = {
      application_admin: null,
      publication_admin: null,
      editor: null,
      review_coordinator: "review_coordinator",
      reviewer: "reviewer",
      submitter: "submitter",
    }
    const submission_effective_role = {
      application_admin: "review_coordinator",
      publication_admin: "review_coordinator",
      editor: "review_coordinator",
      review_coordinator: "review_coordinator",
      reviewer: "reviewer",
      submitter: "submitter",
    }
    const publication_my_role = {
      application_admin: null,
      publication_admin: "publication_admin",
      editor: "editor",
      review_coordinator: null,
      reviewer: null,
      submitter: null,
    }
    return {
      id: id,
      title: "Pilcrow Test Submission for Jest",
      created_at: "2023-06-27T08:21:44.000000Z",
      status: status,
      my_role: submission_my_role[role],
      effective_role: submission_effective_role[role],
      publication: {
        id: "1",
        name: "Pilcrow Test Publication 1",
        my_role: publication_my_role[role],
        editors: [],
        publication_admins: [],
      },
      inline_comments: [],
      overall_comments: [],
      submitters: [],
      reviewers: [],
      review_coordinators: [],
    }
  }

  function getData() {
    return {
      data: {
        currentUser: {
          id: "5",
          roles: [],
          submissions: [],
        },
      },
    }
  }

  it("mounts without errors", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const mockData = getData()
    mockData.data.currentUser.submissions.push(
      mockSubmission("100", "UNDER_REVIEW", "submitter"),
    )
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("the reviews table appears for a user with reviews", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })

    const mockData = getData()
    mockData.data.currentUser.submissions.push(
      mockSubmission("100", "UNDER_REVIEW", "reviewer"),
      mockSubmission("101", "UNDER_REVIEW", "reviewer"),
      mockSubmission("102", "REJECTED", "reviewer"),
    )
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper.findAll('[data-cy="reviews_table"]').length).toBe(1)
    expect(wrapper.findAll('[data-cy="coordinator_table"]').length).toBe(0)
    expect(wrapper.findAll('[data-cy="submissions_table"]').length).toBe(0)
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      1,
    )
  })

  test("the reviews table does not appear for a user with no reviews", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })

    const mockData = getData()
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      0,
    )
  })

  function mockGetSubmissions(role_name) {
    const paginator = {
      count: 1,
      currentPage: 1,
      lastPage: 1,
      perPage: 10,
    }
    const paginator_data = {
      application_admin: paginator,
      publication_admin: paginator,
      editor: paginator,
      review_coordinator: [],
      reviewer: [],
      submitter: [],
    }
    const submission_records = [
      mockSubmission("1000", "UNDER_REVIEW", role_name),
    ]
    const submissions_data = {
      application_admin: submission_records,
      publication_admin: submission_records,
      editor: submission_records,
      review_coordinator: [],
      reviewer: [],
      submitter: [],
    }
    return {
      data: {
        submissions: {
          paginatorInfo: paginator_data[role_name],
          data: submissions_data[role_name],
        },
      },
    }
  }
  function mockCurrentUserSubmissions(role_name) {
    return {
      data: {
        currentUser: {
          id: 1000,
          roles:
            role_name == "application_admin"
              ? [{ name: "Application Administrator" }]
              : [],
          highest_privileged_role: role_name,
          submissions: [mockSubmission("1000", "UNDER_REVIEW", role_name)],
        },
      },
    }
  }

  describe.each([
    ["application_admin"],
    ["publication_admin"],
    ["editor"],
    ["review_coordinator"],
    ["reviewer"],
    ["submitter"],
  ])("a submission table appears", (role_name) => {
    test(`when the user's role is ${role_name}`, async () => {
      mockClient
        .getRequestHandler(GET_SUBMISSIONS)
        .mockResolvedValue(mockGetSubmissions(role_name))

      CurrentUserSubmissions.mockResolvedValue(
        mockCurrentUserSubmissions(role_name),
      )
      const wrapper = await wrapperFactory()
      await flushPromises()
      expect(
        wrapper.findAllComponents({ name: "submission-table" }).length,
      ).toBe(1)
    })
  })
})
