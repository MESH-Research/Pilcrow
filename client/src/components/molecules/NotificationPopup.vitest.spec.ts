import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import type { currentUserNotificationsQuery } from "src/graphql/generated/graphql"
import NotificationPopup from "./NotificationPopup.vue"

import { describe, expect, it, vi, afterEach } from "vitest"

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Nofitication Popup", () => {
  const wrapperFactory = () => mount(NotificationPopup)
  const userNotificationsHandler = vi.fn()
  mockClient.setRequestHandler(
    CURRENT_USER_NOTIFICATIONS,
    userNotificationsHandler
  )

  afterEach(() => {
    userNotificationsHandler.mockReset()
  })

  function mockNotificationResponse(readStatus: boolean): {
    data: currentUserNotificationsQuery
  } {
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
            data: [
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
                created_at: "2021-12-13 20:13:05"
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

  it("displays an indicator for a user that has unread notifications", async () => {
    userNotificationsHandler.mockResolvedValue(mockNotificationResponse(false))

    const wrapper = wrapperFactory()
    const indicator = wrapper.findComponent({ ref: "notification_indicator" })
    expect(indicator).toBeTruthy()
  })

  it("displays no indicator for a user that has no unread notifications", async () => {
    const wrapper = wrapperFactory()
    userNotificationsHandler.mockResolvedValue(mockNotificationResponse(true))

    expect(
      wrapper.findAllComponents({ ref: "notification_indicator" })
    ).toHaveLength(0)
  })

  it("displays no indicator for a user that has no notifications", async () => {
    const wrapper = wrapperFactory()
    userNotificationsHandler.mockResolvedValue([])

    expect(
      wrapper.findAllComponents({ ref: "notification_indicator" })
    ).toHaveLength(0)
  })

  it("provides a default message for a user that has no notifications", async () => {
    const wrapper = wrapperFactory()
    userNotificationsHandler.mockResolvedValue([])
    ;(wrapper.vm as any).isExpanded = true
    await flushPromises()
    const message = wrapper.findComponent({ ref: "default_message" })
    expect(message.text()).toContain("notifications.none")
  })

  it("doesn't provide a default message for a user that has notifications", async () => {
    const wrapper = wrapperFactory()
    userNotificationsHandler.mockResolvedValue(mockNotificationResponse(true))
    ;(wrapper.vm as any).isExpanded = true
    await flushPromises()
    expect(wrapper.findAllComponents({ ref: "default_message" })).toHaveLength(
      0
    )
  })
})
