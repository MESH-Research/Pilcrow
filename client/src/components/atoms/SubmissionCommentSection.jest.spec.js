import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionCommentSection from "./SubmissionCommentSection.vue"
import { ref } from "vue"
import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"

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
                  content:
                    "Commodi ipsam excepturi non excepturi. Dolore quia eum sit neque quibusdam fugiat. Excepturi est enim reprehenderit atque unde rerum eum.",
                  created_at: "2022-06-02T08:18:09Z",
                  created_by: {
                    id: "2",
                    email: "publicationadministrator@ccrproject.dev",
                    name: "Publication Administrator",
                  },
                  replies: [],
                },
                {
                  id: "2",
                  content:
                    "Fugit id officia facere nesciunt modi beatae beatae. Assumenda culpa consequatur vel autem.",
                  created_at: "2022-06-01T20:57:28Z",
                  created_by: {
                    id: "4",
                    email: "reviewcoordinator@ccrproject.dev",
                    name: "Review Coordinator for Submission",
                  },
                  replies: [
                    {
                      id: "3",
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
                  content:
                    "Temporibus voluptatem ea aut placeat atque eum. A officia ea eos quo. Ut dolor sequi deserunt quo.",
                  created_at: "2022-06-02T10:38:18Z",
                  created_by: {
                    id: "2",
                    email: "publicationadministrator@ccrproject.dev",
                    name: "Publication Administrator",
                  },
                  replies: [
                    {
                      id: "5",
                      content:
                        "Nihil beatae omnis illum laborum magni quam quia rerum. Quia doloremque fugit ipsa debitis ratione laborum dolorem.",
                      created_at: "2022-06-03T10:38:18Z",
                      created_by: {
                        id: "4",
                        username: "reviewCoordinator",
                        email: "reviewcoordinator@ccrproject.dev",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "6",
                      content:
                        "Sed nostrum est perferendis labore rem molestiae molestiae. Necessitatibus officiis quia labore et eum harum eveniet. Officia ut accusantium non saepe ut.",
                      created_at: "2022-06-04T10:38:18Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@ccrproject.dev",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "7",
                      content:
                        "Ut labore dignissimos aperiam ipsum et unde velit. Maxime animi quidem perspiciatis nihil possimus qui sequi labore.",
                      created_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "8",
                      content:
                        "Aut numquam harum dolorem aliquam nulla. Ut tempore numquam modi maiores quia iusto.",
                      created_at: "2022-06-04T10:38:18Z",
                      created_by: {
                        id: "2",
                        username: "publicationAdministrator",
                        email: "publicationadministrator@ccrproject.dev",
                      },
                      reply_to_id: "5",
                    },
                    {
                      id: "9",
                      content:
                        "Ut praesentium cumque beatae reiciendis laboriosam quia illum alias. Est quasi corrupti eveniet sequi et. Voluptatem ea ut in sed ipsa officiis et.",
                      created_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "3",
                        username: "publicationEditor",
                        email: "publicationeditor@ccrproject.dev",
                      },
                      reply_to_id: "6",
                    },
                    {
                      id: "10",
                      content:
                        "Consequuntur dignissimos quibusdam eum placeat est. Aut eos nobis accusantium omnis sapiente.",
                      created_at: "2022-06-03T10:38:18Z",
                      created_by: {
                        id: "6",
                        username: "akihn",
                        email: "powlowski.eliza@example.org",
                      },
                      reply_to_id: "4",
                    },
                    {
                      id: "11",
                      content:
                        "Error facere qui vel labore explicabo sint dignissimos. Recusandae minima quia enim fugiat. Suscipit aut voluptate consequatur molestiae omnis sint.",
                      created_at: "2022-06-04T10:38:18Z",
                      created_by: {
                        id: "7",
                        username: "dickens.octavia",
                        email: "olson.noe@example.com",
                      },
                      reply_to_id: "10",
                    },
                    {
                      id: "12",
                      content:
                        "Est incidunt nisi perferendis magni. Voluptatum ex quae quam dicta earum repellendus.",
                      created_at: "2022-06-05T10:38:18Z",
                      created_by: {
                        id: "5",
                        username: "regularUser",
                        email: "regularuser@ccrproject.dev",
                      },
                      reply_to_id: "11",
                    },
                  ],
                },
              ],
            }),
            activeComment: ref(),
          },
          mocks: {
            $t: (token) => token,
          },
          stubs: ["router-link", "CommentEditor"],
        },
      }),
    }
  }

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("expected number of overall comments appear", () => {
    const { wrapper } = wrapperFactory()
    const items = wrapper.findAllComponents('[data-cy="overallComment"]')
    expect(items).toHaveLength(3)
  })

  test("expected number of overall comment replies appear", () => {
    const { wrapper } = wrapperFactory()
    const overallComments = wrapper.findAll('[data-cy="overallComment"]')
    const findRepliesTo = (w) => w.findAll('[data-cy="overallCommentReply"]')

    expect(findRepliesTo(overallComments.at(0))).toHaveLength(0)
    expect(findRepliesTo(overallComments.at(1))).toHaveLength(1)
    expect(findRepliesTo(overallComments.at(2))).toHaveLength(8)
  })
})
