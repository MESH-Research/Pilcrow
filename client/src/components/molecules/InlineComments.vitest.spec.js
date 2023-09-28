import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import InlineComments from "./InlineComments.vue"

import { describe, expect, test, vi } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
describe("InlineComments", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(InlineComments, {
        global: {
          provide: {
            submission: ref({
              id: "1",
              effective_role: "review_coordinator",
              inline_comments: [
                {
                  id: "1",
                  __typename: "InlineComment",
                  from: 1,
                  to: 10,
                  content:
                    "Excepturi consectetur autem temporibus modi. Ipsum unde reiciendis ipsa quidem. Nihil nisi sint ut et.",
                  created_at: "2022-06-01T03:53:17Z",
                  created_by: {
                    id: "2",
                    email: "publicationadministrator@meshresearch.net",
                    name: "Publication Administrator",
                  },
                  replies: [
                    {
                      id: "2",
                      __typename: "InlineCommentReply",
                      content:
                        "Sed ullam culpa dolore ea qui. Enim voluptatem eos voluptas et est.",
                      created_at: "2022-06-02T03:53:17Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdminUser",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "1",
                    },
                  ],
                  style_criteria: [
                    {
                      name: "Relevance",
                      icon: "close_fullscreen",
                    },
                  ],
                },
                {
                  id: "3",
                  __typename: "InlineComment",
                  from: 11,
                  to: 20,
                  content:
                    "Iure itaque non illo et. Et assumenda quasi doloribus natus vitae. Cupiditate aut exercitationem rerum quidem iusto occaecati saepe.",
                  created_at: "2022-05-30T19:20:59Z",
                  created_by: {
                    id: "6",
                    email: "gracie91@example.net",
                    name: "Alessandra Kohler",
                  },
                  replies: [],
                  style_criteria: [
                    {
                      name: "Relevance",
                      icon: "close_fullscreen",
                    },
                    {
                      name: "Coherence",
                      icon: "psychology",
                    },
                    {
                      name: "Scholarly Dialogue",
                      icon: "question_answer",
                    },
                    {
                      name: "Accessibility",
                      icon: "accessibility",
                    },
                  ],
                },
                {
                  id: "4",
                  __typename: "InlineComment",
                  from: 21,
                  to: 30,
                  content:
                    "Quia ea numquam delectus sapiente in molestiae. Omnis placeat impedit doloribus atque. Beatae est repellat officia magnam molestiae similique.",
                  created_at: "2022-05-31T16:28:10Z",
                  created_by: {
                    id: "7",
                    email: "hessel.russell@example.net",
                    name: "Mayra Kuhic",
                  },
                  replies: [
                    {
                      id: "5",
                      __typename: "InlineCommentReply",
                      content:
                        "Repudiandae voluptatem voluptatum in quia quos. Molestiae molestias fugit distinctio sed deserunt culpa itaque. Autem quo accusantium autem eaque autem.",
                      created_at: "2022-06-01T16:28:10Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "6",
                      __typename: "InlineCommentReply",
                      content:
                        "Voluptatem earum deleniti non possimus et libero. Illo a quia est perferendis libero ipsa.",
                      created_at: "2022-06-02T16:28:10Z",
                      created_by: {
                        id: "4",
                        username: "reviewCoordinator",
                        email: "reviewcoordinator@meshresearch.net",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "7",
                      __typename: "InlineCommentReply",
                      content:
                        "Id temporibus quia ut placeat at qui nemo. Dolorum numquam consequatur amet repellat veniam enim aliquam.",
                      created_at: "2022-06-01T16:28:10Z",
                      created_by: {
                        id: "7",
                        username: "rschoen",
                        email: "hessel.russell@example.net",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "8",
                      __typename: "InlineCommentReply",
                      content:
                        "Autem blanditiis labore ducimus rerum assumenda. Quam deleniti nesciunt voluptas aut alias. Magni est ut ea rerum.",
                      created_at: "2022-06-02T16:28:10Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdminUser",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "7",
                    },
                    {
                      id: "9",
                      __typename: "InlineCommentReply",
                      content:
                        "Et velit hic voluptate illo praesentium dicta. Dolores repudiandae qui non et consequatur et et autem. Laudantium qui sint accusantium soluta facilis esse ut.",
                      created_at: "2022-06-01T16:28:10Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdminUser",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "10",
                      __typename: "InlineCommentReply",
                      content:
                        "Odit ut nihil similique accusamus a et deleniti quam. Non voluptates quis ipsa voluptatem. Voluptas reprehenderit id aut ab officia facere.",
                      created_at: "2022-06-03T16:28:10Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdminUser",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "11",
                      __typename: "InlineCommentReply",
                      content:
                        "Et aut voluptates alias dicta ut quis. Illum dolore occaecati quia ut excepturi autem. Rem ratione molestias perspiciatis est.",
                      created_at: "2022-06-02T16:28:10Z",
                      created_by: {
                        id: "1",
                        username: "applicationAdminUser",
                        email: "applicationadministrator@meshresearch.net",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "12",
                      __typename: "InlineCommentReply",
                      content:
                        "Rerum est aut ratione expedita vitae earum. Vel aliquid eum quia corporis quidem nisi enim consequatur.",
                      created_at: "2022-06-03T16:28:10Z",
                      created_by: {
                        id: "5",
                        username: "regularUser",
                        email: "regularuser@meshresearch.net",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "13",
                      __typename: "InlineCommentReply",
                      content:
                        "Aut est unde dolores assumenda. Cum consectetur corrupti quaerat vel ab ea hic. Quasi repellat dolor ducimus omnis.",
                      created_at: "2022-06-02T16:28:10Z",
                      created_by: {
                        id: "4",
                        username: "reviewCoordinator",
                        email: "reviewcoordinator@meshresearch.net",
                      },
                      reply_to_id: "7",
                    },
                    {
                      id: "14",
                      __typename: "InlineCommentReply",
                      content:
                        "Porro minima et laboriosam non. Perspiciatis quis doloremque ut saepe neque.",
                      created_at: "2022-06-01T16:28:10Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@meshresearch.net",
                      },
                      reply_to_id: "4",
                    },
                  ],
                  style_criteria: [
                    {
                      name: "Accessibility",
                      icon: "accessibility",
                    },
                    {
                      name: "Scholarly Dialogue",
                      icon: "question_answer",
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

  test("expected style criteria appear within all inline comments", () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    const items = wrapper.findAllComponents('[data-cy="inlineComment"]')
    const findStyleCriteria = (w) =>
      w.findAllComponents('[data-cy="styleCriteria"]')
    expect(findStyleCriteria(items.at(0))).toHaveLength(1)
    expect(findStyleCriteria(items.at(1))).toHaveLength(4)
    expect(findStyleCriteria(items.at(2))).toHaveLength(2)
  })

  test("expected number of inline comments appear", () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    const items = wrapper.findAllComponents('[data-cy="inlineComment"]')
    expect(items).toHaveLength(3)
  })

  test("expected number of inline comment replies appear", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const { wrapper } = wrapperFactory()
    const items = wrapper.findAllComponents('[data-cy="inlineComment"]')
    const findReplies = (w) => w.findAll('[data-cy="inlineCommentReply"]')
    await items.at(0).find('[data-cy="showRepliesButton"]').trigger("click")

    expect(findReplies(items.at(0))).toHaveLength(1)

    expect(items.at(1).find("[data-cy=showRepliesButton]").exists()).toBe(false)

    await items.at(2).find('[data-cy="showRepliesButton"]').trigger("click")
    expect(findReplies(items.at(2))).toHaveLength(10)
  })
})
