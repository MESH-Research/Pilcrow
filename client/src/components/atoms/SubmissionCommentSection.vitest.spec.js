import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import SubmissionCommentSection from "./SubmissionCommentSection.vue"

import { describe, expect, test, vi } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
describe("Overall Comments", () => {
  TimeAgo.addDefaultLocale(en)
  const wrapperFactory = () => {
    return {
      wrapper: mount(SubmissionCommentSection, {
        global: {
          provide: {
            submission: ref({
              id: "1",
              overall_comments: [
                {
                  id: "1",
                  __typename: "OverallComment",
                  content:
                    "Commodi ipsam excepturi non excepturi. Dolore quia eum sit neque quibusdam fugiat. Excepturi est enim reprehenderit atque unde rerum eum.",
                  created_at: "2022-06-02T08:18:09Z",
                  created_by: {
                    id: "2",
                    email: "publicationadministrator@meshresearch.net",
                    name: "Publication Administrator",
                  },
                  replies: [],
                },
                {
                  id: "2",
                  __typename: "OverallComment",
                  content:
                    "Fugit id officia facere nesciunt modi beatae beatae. Assumenda culpa consequatur vel autem.",
                  created_at: "2022-06-01T20:57:28Z",
                  created_by: {
                    id: "4",
                    email: "reviewcoordinator@meshresearch.net",
                    name: "Review Coordinator for Submission",
                  },
                  replies: [
                    {
                      id: "3",
                      __typename: "OverallCommentReply",
                      content:
                        "Asperiores aut commodi dolorum enim iusto consequatur minima. Neque adipisci animi tempora voluptatem error nam cupiditate.",
                      created_at: "2022-06-02T20:57:28Z",
                      created_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      reply_to_id: "2",
                    },
                  ],
                },
                {
                  id: "4",
                  __typename: "OverallComment",
                  content:
                    "Temporibus voluptatem ea aut placeat atque eum. A officia ea eos quo. Ut dolor sequi deserunt quo.",
                  created_at: "2022-06-02T10:38:18Z",
                  updated_at: "2022-06-02T10:38:18Z",
                  created_by: {
                    id: "1",
                    email: "applicationadministrator@meshresearch.net",
                    name: "Application Administrator",
                  },
                  updated_by: {
                    id: "1",
                    email: "applicationadministrator@meshresearch.net",
                    name: "Application Administrator",
                  },
                  replies: [
                    {
                      id: "5",
                      __typename: "OverallCommentReply",
                      content:
                        "Nihil beatae omnis illum laborum magni quam quia rerum. Quia doloremque fugit ipsa debitis ratione laborum dolorem.",
                      created_at: "2022-06-03T10:38:18Z",
                      updated_at: "2022-06-03T10:38:18Z",
                      created_by: {
                        id: "4",
                        username: "reviewCoordinator",
                        email: "reviewcoordinator@meshresearch.net",
                      },
                      updated_by: {
                        id: "4",
                        username: "reviewCoordinator",
                        email: "reviewcoordinator@meshresearch.net",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "6",
                      __typename: "OverallCommentReply",
                      content:
                        "Sed nostrum est perferendis labore rem molestiae molestiae. Necessitatibus officiis quia labore et eum harum eveniet. Officia ut accusantium non saepe ut.",
                      created_at: "2022-06-04T10:38:18Z",
                      updated_at: "2022-06-04T10:38:18Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      updated_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "7",
                      __typename: "OverallCommentReply",
                      content:
                        "Ut labore dignissimos aperiam ipsum et unde velit. Maxime animi quidem perspiciatis nihil possimus qui sequi labore.",
                      created_at: "2022-06-05T10:38:18Z",
                      updated_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      updated_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "8",
                      __typename: "OverallCommentReply",
                      content:
                        "Aut numquam harum dolorem aliquam nulla. Ut tempore numquam modi maiores quia iusto.",
                      created_at: "2022-06-04T10:38:18Z",
                      updated_at: "2022-08-04T05:38:18Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdministrator",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      updated_by: {
                        id: "1",
                        username: "applicationAdministrator",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "9",
                      __typename: "OverallCommentReply",
                      content:
                        "Ut praesentium cumque beatae reiciendis laboriosam quia illum alias. Est quasi corrupti eveniet sequi et. Voluptatem ea ut in sed ipsa officiis et.",
                      created_at: "2022-06-05T10:38:18Z",
                      updated_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      updated_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "10",
                      __typename: "OverallCommentReply",
                      content:
                        "Consequuntur dignissimos quibusdam eum placeat est. Aut eos nobis accusantium omnis sapiente.",
                      created_at: "2022-06-03T10:38:18Z",
                      updated_at: "2022-06-03T10:38:18Z",
                      created_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      updated_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "11",
                      __typename: "OverallCommentReply",
                      content:
                        "Error facere qui vel labore explicabo sint dignissimos. Recusandae minima quia enim fugiat. Suscipit aut voluptate consequatur molestiae omnis sint.",
                      created_at: "2022-06-04T10:38:18Z",
                      updated_at: "2022-06-04T10:38:18Z",
                      created_by: {
                        id: "7",
                        username: "dickens.octavia",
                        email: "olson.noe@example.com",
                      },
                      updated_by: {
                        id: "7",
                        username: "dickens.octavia",
                        email: "olson.noe@example.com",
                      },
                      reply_to_id: "10",
                    },
                    {
                      id: "12",
                      __typename: "OverallCommentReply",
                      content:
                        "Est incidunt nisi perferendis magni. Voluptatum ex quae quam dicta earum repellendus.",
                      created_at: "2022-06-05T10:38:18Z",
                      updated_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "5",
                        username: "regularUser",
                        email: "regularuser@meshresearch.net",
                      },
                      updated_by: {
                        id: "5",
                        username: "regularUser",
                        email: "regularuser@meshresearch.net",
                      },
                      reply_to_id: "11",
                    },
                  ],
                },
              ],
            }),
            activeComment: ref(),
          },
          stubs: ["router-link", "CommentEditor"],
        },
      }),
    }
  }

  test("able to mount", () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("expected number of overall comments appear", () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    const items = wrapper.findAllComponents('[data-cy="overallComment"]')
    expect(items).toHaveLength(3)
  })

  test("expected number of overall comment replies appear", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    const overallComments = wrapper.findAll('[data-cy="overallComment"]')
    const findReplies = (w) => w.findAll('[data-cy="overallCommentReply"]')

    // First Overall Comment
    expect(
      overallComments.at(0).find("[data-cy=showRepliesButton]").exists()
    ).toBe(false)
    expect(findReplies(overallComments.at(0))).toHaveLength(0)

    // Second Overall Comment
    await overallComments
      .at(1)
      .find('[data-cy="showRepliesButton"]')
      .trigger("click")
    expect(findReplies(overallComments.at(1))).toHaveLength(1)

    // Third Overall Comment
    await overallComments
      .at(2)
      .find('[data-cy="showRepliesButton"]')
      .trigger("click")
    expect(findReplies(overallComments.at(2))).toHaveLength(8)
    await overallComments
      .at(2)
      .find('[data-cy="hideRepliesButton"]')
      .trigger("click")
    expect(findReplies(overallComments.at(2))).toHaveLength(0)
  })
  test("expected timestamp is shown for created and updated overall comment replies", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()

    const overallComments = wrapper.findAll('[data-cy="overallComment"]')
    await overallComments
      .at(2)
      .find('[data-cy="showRepliesButton"]')
      .trigger("click")
    const overallCommentReplies = wrapper.findAll(
      '[data-cy="overallCommentReply"]'
    )
    expect(
      overallCommentReplies.at(3).find('[data-cy="timestampUpdated"]').exists()
    ).toBe(true)
  })
})
