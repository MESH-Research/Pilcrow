import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { ref } from "vue"
import { useCurrentUser } from "src/use/user"
import ReviewsPage from "./ReviewsPage.vue"

import { describe, expect, test, vi, afterEach } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

useCurrentUser.mockReturnValue({
  currentUser: ref({ id: 1 }),
})

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Reviews Page", () => {
  const makeWrapper = () =>
    mount(ReviewsPage, {
      global: {
        stubs: ["router-link", "i18n-t"],
      },
    })

  afterEach(() => {
    vi.clearAllMocks()
  })

  function mockSubmission(role_name) {
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
      id: "1001",
      title: "Jest Submission 1001",
      created_at: "2023-11-09T18:51:10.000000Z",
      status: "UNDER_REVIEW",
      my_role: submission_my_role[role_name],
      effective_role: submission_effective_role[role_name],
      publication: {
        id: "1000",
        name: "Jest Publication",
        my_role: publication_my_role[role_name],
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
    const submission_records = [mockSubmission(role_name)]
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
    const submission_records = [mockSubmission(role_name)]
    const submissions_data = {
      application_admin: submission_records,
      publication_admin: submission_records,
      editor: submission_records,
      review_coordinator: submission_records,
      reviewer: submission_records,
      submitter: [],
    }
    return {
      data: {
        currentUser: {
          id: 1000,
          roles:
            role_name == "application_admin"
              ? [{ name: "Application Administrator" }]
              : [],
          highest_privileged_role: role_name,
          submissions: submissions_data[role_name],
        },
      },
    }
  }

  describe.each([
    ["application_admin", 2],
    ["publication_admin", 2],
    ["editor", 2],
    ["review_coordinator", 2],
    ["reviewer", 1],
    ["submitter", 1],
  ])("when the user's role is %s", (role_name, expected) => {
    test(`${expected} submission tables appear`, async () => {
      mockClient
        .getRequestHandler(GET_SUBMISSIONS)
        .mockResolvedValue(mockGetSubmissions(role_name))

      mockClient
        .getRequestHandler(CURRENT_USER_SUBMISSIONS)
        .mockResolvedValue(mockCurrentUserSubmissions(role_name))
      const wrapper = makeWrapper()
      await flushPromises()
      expect(
        wrapper.findAllComponents({ name: "submission-table" }).length,
      ).toBe(expected)
    })
  })
})
