import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import FeedPage from "../pages/(main)/feed.vue"

import { afterEach, describe, expect, it, vi } from "vitest"

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Nofitication Popup", () => {
  const wrapperFactory = () => mount(FeedPage)

  const userNotificationsHandler = vi.fn()
  mockClient.setRequestHandler(
    CURRENT_USER_NOTIFICATIONS,
    userNotificationsHandler
  )

  afterEach(() => {
    userNotificationsHandler.mockClear()
  })

  function getNotificationData(readStatus, data) {
    return {
      data: {
        currentUser: {
          id: 1,
          notifications: {
            paginatorInfo: {
              __typename: "PaginatorInfo",
              count: 1,
              currentPage: 1,
              lastPage: 1,
              perPage: 10
            },
            data: data ?? [
              {
                id: "df91c4c0-3cf2-409d-a305-9a6005149c86",
                type: "AppNotificationsSubmissionStatusUpdated",
                notifiable_type: "AppModelsUser",
                notifiable_id: 1,
                data: {
                  submission: {
                    id: 9999,
                    title: "A Modest Proposal"
                  },
                  publication: {
                    id: 9999,
                    name: "Test Publication from Tinker"
                  },
                  user: { id: 9999, username: "Test User from Tinker" },
                  type: "submission.awaiting_review",
                  body: "A submission status has been accepted for review.",
                  action: "Visit Pilcrow",
                  url: "/",
                  invitee: {
                    display_label: "Test User"
                  },
                  inviter: {
                    display_label: "Test User"
                  }
                },
                read_at: readStatus ? "2021-12-31 12:15:15" : null,
                created_at: "2021-12-13T20:13:05.000",
                updated_at: "2021-12-15T16:31:18.000"
              }
            ]
          }
        }
      }
    }
  }

  it("mounts without errors", () => {
    userNotificationsHandler.mockResolvedValue(getNotificationData(false))
    const wrapper = wrapperFactory()

    expect(wrapper).toBeTruthy()
  })

  it("displays a default message for a user that has no notifications", async () => {
    userNotificationsHandler.mockResolvedValue(getNotificationData(true, []))

    const wrapper = wrapperFactory()
    const message = wrapper.findComponent({ ref: "default_message" })
    expect(message.text()).toContain("notifications.none")
  })

  it("does not display the default message for a user that has notifications", async () => {
    userNotificationsHandler.mockResolvedValue(getNotificationData(true))

    const wrapper = wrapperFactory()
    expect(wrapper.findAllComponents({ ref: "default_message" })).toHaveLength(
      0
    )
  })
})
