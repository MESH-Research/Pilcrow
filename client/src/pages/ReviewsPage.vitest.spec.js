import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import ReviewsPage from "./ReviewsPage.vue"

import { describe, expect, test, vi, afterEach } from "vitest"

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Reviews Page", () => {
  const makeWrapper = () => mount(ReviewsPage, {
    global: {
      stubs: ["router-link"],
    },
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  test("two submission tables appear for a user as a review coordinator", async () => {
    mockClient
      .getRequestHandler(CURRENT_USER_SUBMISSIONS)
      .mockResolvedValue({
        data: {
          currentUser: {
            id: 1000,
            roles: {
              name: "Application Administrator",
            },
            submissions: [
              {
                id: "1",
                title: "Jest Submission 1",
                status: "INITIALLY_SUBMITTED",
                my_role: "submitter",
                effective_role: "submitter",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
                status: "RESUBMISSION_REQUESTED",
                my_role: "reviewer",
                effective_role: "reviewer",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
                id: "3",
                title: "Jest Submission 3",
                status: "AWAITING_REVIEW",
                my_role: "review_coordinator",
                effective_role: "review_coordinator",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
                status: "REJECTED",
                my_role: "review_coordinator",
                effective_role: "review_coordinator",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
                status: "INITIALLY_SUBMITTED",
                my_role: "reviewer",
                effective_role: "reviewer",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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

    const wrapper = makeWrapper()
    await flushPromises()

    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      2
    )
  })

  test("only one submission table appears for a user as a reviewer", async () => {
    mockClient
      .getRequestHandler(CURRENT_USER_SUBMISSIONS)
      .mockResolvedValue({
        data: {
          currentUser: {
            id: 1000,
            roles: {
              name: "Application Administrator",
            },
            submissions: [
              {
                id: "1",
                title: "Jest Submission 1",
                status: "INITIALLY_SUBMITTED",
                my_role: "submitter",
                effective_role: "submitter",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
                status: "RESUBMISSION_REQUESTED",
                my_role: "reviewer",
                effective_role: "reviewer",
                publication: {
                  id: "1",
                  name: "Jest Publication",
                  my_role: null,
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
    const wrapper = makeWrapper()
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      1
    )
  })
})
