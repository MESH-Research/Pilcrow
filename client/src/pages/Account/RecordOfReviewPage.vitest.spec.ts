import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { GET_RECORDS_OF_REVIEW } from "src/graphql/queries"
import RecordOfReviewPage from "./RecordOfReviewPage.vue"
import type {
  GetRecordsOfReviewQuery,
  SubmissionStatus
} from "src/graphql/generated/graphql"

import { beforeEach, describe, expect, it, vi } from "vitest"

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
          submissions: [
            {
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
                  }
                }
              ],
              title: "Hello World",
              status: "ACCEPTED_AS_FINAL" as SubmissionStatus,
              created_at: "2026-05-05T17:31:52.000000Z",
              submitted_at: null,
              submitters: [
                {
                  id: "1",
                  display_label: "Application Administrator",
                  username: "applicationAdminUser",
                  name: "Application Administrator",
                  email: "applicationadministrator@meshresearch.net",
                  staged: null,
                  __typename: "User"
                }
              ],
              reviewers: [
                {
                  id: "5",
                  display_label: "Reviewer for Submission",
                  username: "reviewer",
                  name: "Reviewer for Submission",
                  email: "reviewer@meshresearch.net",
                  staged: null,
                  __typename: "User",
                  profile_metadata: null
                }
              ],
              review_coordinators: [
                {
                  id: "4",
                  display_label: "Review Coordinator for Submission",
                  username: "reviewCoordinator",
                  name: "Review Coordinator for Submission",
                  email: "reviewcoordinator@meshresearch.net",
                  staged: null,
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
            }
          ]
        },
        submissions: {
          paginatorInfo: {
            count: 14,
            currentPage: 1,
            lastPage: 1,
            perPage: 100,
            __typename: "PaginatorInfo"
          },
          data: [
            {
              id: "109",
              audits: [
                {
                  id: "27",
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
                    status: "DRAFT" as SubmissionStatus,
                    status_change_comment: null,
                    title: "Pilcrow Test Submission 10",
                    __typename: "SubmissionAuditValues"
                  },
                  __typename: "SubmissionAudit"
                },
                {
                  id: "28",
                  created_at: "2026-05-05T17:31:52.000000Z",
                  event: "updated",
                  old_values: {
                    content_id: null,
                    status: null,
                    status_change_comment: null,
                    title: null,
                    __typename: "SubmissionAuditValues"
                  },
                  new_values: {
                    content_id: "27",
                    status: null,
                    status_change_comment: null,
                    title: null,
                    __typename: "SubmissionAuditValues"
                  },
                  __typename: "SubmissionAudit"
                },
                {
                  id: "29",
                  created_at: "2026-05-05T17:31:52.000000Z",
                  event: "updated",
                  old_values: {
                    content_id: null,
                    status: "DRAFT" as SubmissionStatus,
                    status_change_comment: null,
                    title: null,
                    __typename: "SubmissionAuditValues"
                  },
                  new_values: {
                    content_id: null,
                    status: "ARCHIVED" as SubmissionStatus,
                    status_change_comment: null,
                    title: null,
                    __typename: "SubmissionAuditValues"
                  },
                  __typename: "SubmissionAudit"
                }
              ],
              title: "Pilcrow Test Submission 10",
              status: "ARCHIVED" as SubmissionStatus,
              created_at: "2026-05-05T17:31:52.000000Z",
              submitted_at: "2026-05-05T17:31:52.000000Z",
              submitters: [
                {
                  id: "6",
                  display_label: "Regular User",
                  username: "regularUser",
                  name: "Regular User",
                  email: "regularuser@meshresearch.net",
                  staged: null,
                  __typename: "User"
                }
              ],
              reviewers: [
                {
                  id: "5",
                  display_label: "Reviewer for Submission",
                  username: "reviewer",
                  name: "Reviewer for Submission",
                  email: "reviewer@meshresearch.net",
                  staged: null,
                  __typename: "User",
                  profile_metadata: null
                }
              ],
              review_coordinators: [
                {
                  id: "4",
                  display_label: "Review Coordinator for Submission",
                  username: "reviewCoordinator",
                  name: "Review Coordinator for Submission",
                  email: "reviewcoordinator@meshresearch.net",
                  staged: null,
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
            }
          ]
        }
      }
    }
    requestHandler.mockResolvedValue(RecordsOfReview)
  })

  const requestHandler = vi.fn()
  mockClient.setRequestHandler(GET_RECORDS_OF_REVIEW, requestHandler)

  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })
})
