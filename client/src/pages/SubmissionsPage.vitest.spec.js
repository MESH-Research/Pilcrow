import SubmissionsPage from "./SubmissionsPage.vue"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { afterEach, beforeEach, describe, expect, test, vi } from "vitest"
import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ref } from "vue"
import { useCurrentUser } from "src/use/user"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Submissions Page", () => {
  const CurrentUserSubmissions = vi.fn()
  mockClient.setRequestHandler(CURRENT_USER_SUBMISSIONS, CurrentUserSubmissions)

  beforeEach(async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        id: 1,
        roles: [{
          name: "Application Administrator"
        }],
       }),
      isAppAdmin: ref(true),
      isSubmitter: () => true,
      isReviewCoordinator: () => false,
      isEditor: () => false,
      isPublicationAdmin: () => false,
    })
  })

  afterEach(() => {
    CurrentUserSubmissions.mockClear()
  })

  const wrapperFactory = async () => {
    const wrapper = mount(SubmissionsPage, {
      global: {
        stubs: ["router-link", "i18n-t"],
      },
    })
    await flushPromises()
    return wrapper
  }

  test("able to mount", async () => {
    const wrapper = await wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("all expected submissions appear", async () => {
    CurrentUserSubmissions.mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: [],
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              created_at: "2023-06-27T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              created_at: "2023-06-26T08:21:44.000000Z",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
              editors: [],
              publication_admins: [],
            },
            {
              id: "3",
              title: "Jest Submission 3",
              created_at: "2023-06-25T08:21:44.000000Z",
              status: "AWAITING_REVIEW",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "4",
              title: "Jest Submission 4",
              created_at: "2023-06-24T08:21:44.000000Z",
              status: "REJECTED",
              my_role: "",
              effective_role: "review_coordinator",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "5",
              title: "Jest Submission 5",
              created_at: "2023-06-23T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "",
              effective_role: "",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "6",
              title: "Jest Submission 6",
              created_at: "2023-06-22T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })
    const wrapper = await wrapperFactory()
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      1
    )
    expect(wrapper.findAll('[data-cy="submission_link_desktop"]').length).toBe(
      2
    )
  })

  test("comment previews appear within the latest comments section", async () => {
    CurrentUserSubmissions.mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: [],
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              created_at: "2023-01-27T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [
                {
                  id: "1",
                  content: "Jest Inline Comment 1",
                  style_criteria: [
                    { id: 1, name: "Jest Style Criteria", icon: "home" },
                    { id: 2, name: "Jest Style Criteria", icon: "search" },
                  ],
                  created_by: {
                    id: "1",
                    display_label: "Application Administrator",
                    username: "applicationAdminUser",
                    email: "applicationadministrator@meshresearch.net",
                  },
                  created_at: "2023-03-24T02:01:00.000000Z",
                  updated_by: {
                    id: "1",
                    display_label: "Application Administrator",
                    username: "applicationAdminUser",
                    email: "applicationadministrator@meshresearch.net",
                  },
                  updated_at: "2023-03-24T02:01:00.000000Z",
                  replies: [],
                },
                {
                  id: "2",
                  content: "Jest Inline Comment 2",
                  style_criteria: [
                    { id: 6, name: "Jest Style Criteria", icon: "info" },
                  ],
                  created_by: {
                    id: "3",
                    display_label: "Publication Editor",
                    username: "publicationEditor",
                    email: "publicationEditor@meshresearch.net",
                  },
                  created_at: "2023-03-25T05:27:07.000000Z",
                  updated_by: {
                    id: "3",
                    display_label: "Publication Editor",
                    username: "publicationEditor",
                    email: "publicationEditor@meshresearch.net",
                  },
                  updated_at: "2023-03-25T05:27:07.000000Z",
                  replies: [],
                },
              ],
              overall_comments: [
                {
                  id: "1",
                  content: "Jest Overall Comment 1",
                  created_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  created_at: "2023-03-27T08:21:44.000000Z",
                  updated_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  updated_at: "2023-03-27T08:21:44.000000Z",
                  replies: [],
                },
              ],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              created_at: "2023-01-26T08:21:44.000000Z",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: 1,
                name: "Jest Publication",
                my_role: "",
                editors: [],
                publication_admins: [],
              },
              inline_comments: [
                {
                  id: "3",
                  content: "Jest Inline Comment 3",
                  style_criteria: [
                    { id: 12, name: "Jest Style Criteria", icon: "visibility" },
                  ],
                  created_by: {
                    id: "3",
                    display_label: "Publication Editor",
                    username: "publicationEditor",
                    email: "publicationEditor@meshresearch.net",
                  },
                  created_at: "2023-04-08T02:01:03.000000Z",
                  updated_by: {
                    id: "3",
                    display_label: "Publication Editor",
                    username: "publicationEditor",
                    email: "publicationEditor@meshresearch.net",
                  },
                  updated_at: "2023-04-08T02:01:03.000000Z",
                  replies: [],
                },
                {
                  id: "4",
                  content: "Jest Inline Comment 4",
                  style_criteria: [
                    {
                      id: 16,
                      name: "Jest Style Criteria",
                      icon: "description",
                    },
                  ],
                  created_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  created_at: "2023-04-09T02:25:03.000000Z",
                  updated_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  updated_at: "2023-04-09T02:25:03.000000Z",
                  replies: [],
                },
              ],
              overall_comments: [
                {
                  id: "2",
                  content: "Jest Overall Comment 2",
                  created_by: {
                    id: "1",
                    display_label: "Application Administrator",
                    username: "applicationAdminUser",
                    email: "applicationadministrator@meshresearch.net",
                  },
                  created_at: "2023-04-10T07:12:35.000000Z",
                  updated_by: {
                    id: "1",
                    display_label: "Application Administrator",
                    username: "applicationAdminUser",
                    email: "applicationadministrator@meshresearch.net",
                  },
                  updated_at: "2023-04-10T07:12:35.000000Z",
                  replies: [],
                },
                {
                  id: "3",
                  content: "Jest Overall Comment 3",
                  created_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  created_at: "2023-04-07T05:25:03.000000Z",
                  updated_by: {
                    id: "5",
                    display_label: "Reviewer for Submission",
                    username: "reviewer",
                    email: "reviewer@meshresearch.net",
                  },
                  updated_at: "2023-04-08T05:25:03.000000Z",
                  replies: [],
                },
              ],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })
    const wrapper = await wrapperFactory()
    expect(wrapper.findAllComponents({ name: "comment-preview" }).length).toBe(
      3
    )
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
    ["review_coordinator", 1],
    ["reviewer", 1],
    ["submitter", 1],
  ])("when the user's role is %s", (role_name, expected) => {
    const grammar = expected == 1 ? "table appears" : "tables appear"
    test(`${expected} submission ${grammar}`, async () => {
      mockClient
        .getRequestHandler(GET_SUBMISSIONS)
        .mockResolvedValue(mockGetSubmissions(role_name))

      CurrentUserSubmissions.mockResolvedValue(
        mockCurrentUserSubmissions(role_name)
      )
      const wrapper = await wrapperFactory()
      await flushPromises()
      expect(
        wrapper.findAllComponents({ name: "submission-table" }).length,
      ).toBe(expected)
    })
  })
})
