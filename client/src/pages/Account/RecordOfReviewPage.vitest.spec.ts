import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import {
  GetRecordsOfReviewDocument,
  type GetRecordsOfReviewQuery,
  type SubmissionStatus,
  type SubmissionUserRoles
} from "src/graphql/generated/graphql"
import RecordOfReviewPage from "./RecordOfReviewPage.vue"

import { beforeEach, describe, expect, it, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRoute: vi.fn(() => ({
    query: {}
  })),
  useRouter: vi.fn(() => ({
    push: vi.fn(),
    replace: vi.fn()
  }))
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Record of Review page", () => {
  const makeWrapper = async () => {
    const wrapper = mount(RecordOfReviewPage, {
      global: {
        stubs: ["router-link", "i18n-t"]
      }
    })
    await flushPromises()
    return wrapper
  }

  beforeEach(() => {
    vi.resetAllMocks()
    const RecordsOfReview: { data: GetRecordsOfReviewQuery } = {
      data: {
        currentUser: {
          id: "1",
          submissions: {
            paginatorInfo: {
              count: 1,
              currentPage: 1,
              lastPage: 1,
              perPage: 25,
              total: 1,
              __typename: "PaginatorInfo"
            },
            data: [
              {
                id: "1",
                role: "submitter" as SubmissionUserRoles,
                user: {
                  id: "1",
                  display_label: "Application Administrator",
                  name: "Application Administrator",
                  email: "applicationadministrator@meshresearch.net",
                  profile_metadata: null,
                  __typename: "User"
                },
                submission: {
                  id: "1",
                  audits: [
                    {
                      id: "35",
                      created_at: "2026-05-05T17:31:52.000000Z",
                      event: "created",
                      old_values: {
                        content_id: null,
                        status: null,
                        status_change_comment: null,
                        title: null,
                        __typename: "SubmissionAuditValues"
                      },
                      new_values: {
                        content_id: null,
                        status: "ACCEPTED_AS_FINAL" as SubmissionStatus,
                        status_change_comment: null,
                        title: "Hello World",
                        __typename: "SubmissionAuditValues"
                      },
                      __typename: "SubmissionAudit"
                    }
                  ],
                  title: "Hello World",
                  status: "ACCEPTED_AS_FINAL" as SubmissionStatus,
                  updated_at: "2026-05-05T17:31:52.000000Z",
                  submitters: [
                    {
                      id: "1",
                      username: "applicationAdminUser",
                      name: "Application Administrator",
                      email: "applicationadministrator@meshresearch.net",
                      __typename: "User"
                    }
                  ],
                  reviewers: [
                    {
                      id: "5",
                      display_label: "Reviewer for Submission",
                      profile_metadata: null,
                      __typename: "User"
                    }
                  ],
                  review_coordinators: [
                    {
                      id: "4",
                      display_label: "Review Coordinator for Submission",
                      profile_metadata: null,
                      __typename: "User"
                    }
                  ],
                  publication: {
                    id: "1",
                    name: "Pilcrow Test Publication 1",
                    editors: [
                      {
                        id: "3",
                        display_label: "Publication Editor",
                        username: "publicationEditor",
                        name: "Publication Editor",
                        email: "publicationeditor@meshresearch.net",
                        staged: null,
                        __typename: "User"
                      }
                    ],
                    publication_admins: [
                      {
                        id: "2",
                        display_label: "Publication Administrator",
                        username: "publicationAdministrator",
                        name: "Publication Administrator",
                        email: "publicationadministrator@meshresearch.net",
                        staged: null,
                        __typename: "User"
                      }
                    ],
                    __typename: "Publication"
                  },
                  __typename: "Submission"
                },
                __typename: "SubmissionAssignment"
              }
            ],
            __typename: "SubmissionAssignmentPaginator"
          },
          __typename: "User"
        }
      }
    }
    requestHandler.mockResolvedValue(RecordsOfReview)
  })

  const requestHandler = vi.fn()
  mockClient.setRequestHandler(GetRecordsOfReviewDocument, requestHandler)

  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })
})
