import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import type { currentUserNotificationsQuery } from "src/graphql/generated/graphql"
import FeedPage from "./FeedPage.vue"

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

  type NotificationData = NonNullable<
    currentUserNotificationsQuery["currentUser"]
  >["notifications"]["data"]

  function mockNotificationResponse(
    readStatus: boolean,
    data?: NotificationData
  ): { data: currentUserNotificationsQuery } {
    return {
      data: {
        currentUser: {
          id: "1",
          notifications: {
            paginatorInfo: {
              count: 1,
              currentPage: 1,
              lastPage: 1,
              perPage: 10
            },
            data: data ?? [
              {
                id: "df91c4c0-3cf2-409d-a305-9a6005149c86",
                data: {
                  submission: {
                    title: "A Modest Proposal"
                  },
                  publication: {
                    name: "Test Publication from Tinker"
                  },
                  user: { username: "Test User from Tinker" },
                  type: "submission.awaiting_review",
                  body: "A submission status has been accepted for review.",
                  commenter: {
                    display_label: "Test User"
                  },
                  invitee: {
                    display_label: "Test User"
                  },
                  inviter: {
                    display_label: "Test User"
                  }
                },
                read_at: readStatus ? "2021-12-31 12:15:15" : null,
                created_at: "2021-12-13T20:13:05.000"
              }
            ]
          }
        }
      }
    }
  }

  it("mounts without errors", () => {
    userNotificationsHandler.mockResolvedValue(mockNotificationResponse(false))
    const wrapper = wrapperFactory()

    expect(wrapper).toBeTruthy()
  })

  it("displays a default message for a user that has no notifications", async () => {
    userNotificationsHandler.mockResolvedValue(
      mockNotificationResponse(true, [])
    )

    const wrapper = wrapperFactory()
    const message = wrapper.findComponent({ ref: "default_message" })
    expect(message.text()).toContain("notifications.none")
  })

  it("does not display the default message for a user that has notifications", async () => {
    userNotificationsHandler.mockResolvedValue(mockNotificationResponse(true))

    const wrapper = wrapperFactory()
    expect(wrapper.findAllComponents({ ref: "default_message" })).toHaveLength(
      0
    )
  })
})
