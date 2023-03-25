import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount, flushPromises } from "@vue/test-utils"
import { createMockClient } from "test/vitest/apolloClient"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import FeedPage from "./FeedPage.vue"

import { describe, expect, it, vi } from "vitest"

installQuasarPlugin()
describe("Nofitication Popup", () => {
  const wrapperFactory = (mocks = []) => {
    const mockClient = createMockClient()

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    })

    return {
      wrapper: mount(FeedPage, {
        global: {
          provide: {
            [ApolloClients]: { default: mockClient },
          },
          mocks: {
            $t: (t) => t,
          },
        },
      }),
      mockClient,
    }
  }

  function getNotificationData(readStatus) {
    const data = [
      {
        data: {
          id: "df91c4c0-3cf2-409d-a305-9a6005149c86",
          type: "AppNotificationsSubmissionStatusUpdated",
          notifiable_type: "AppModelsUser",
          notifiable_id: 1,
          data: `"{"submission":{"id":9999,"title":"A Modest Proposal"},"publication":{"id":9999,"name":"Test Publication from Tinker"},"user":{"id":9999,"username":"Test User from Tinker"},"type":"submission.awaiting_review","body":"A submission status has been accepted for review.","action":"Visit Pilcrow","url":"/"}"`,
          read_at: readStatus ? "2021-12-31 12:15:15" : null,
          created_at: "2021-12-13 20:13:05",
          updated_at: "2021-12-15 16:31:18",
        },
      },
    ]
    return data
  }

  it("mounts without errors", () => {
    const { mockClient } = wrapperFactory()
    const queryHandler = vi.fn().mockResolvedValue(getNotificationData(false))
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    expect(wrapperFactory().wrapper).toBeTruthy()
  })

  it("displays a default message for a user that has no notifications", async () => {
    const { wrapper, mockClient } = wrapperFactory()
    const queryHandler = vi.fn().mockResolvedValue([])
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    await flushPromises()
    const message = wrapper.findComponent({ ref: "default_message" })
    expect(message.text()).toContain("notifications.none")
  })

  it("does not display the default message for a user that has notifications", async () => {
    const { wrapper, mockClient } = wrapperFactory()
    const queryHandler = vi.fn().mockResolvedValue(getNotificationData(true))
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    await flushPromises()
    expect(wrapper.findAllComponents({ ref: "default_message" })).toHaveLength(
      0
    )
  })
})
