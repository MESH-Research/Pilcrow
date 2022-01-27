import NotificationPopup from "./NotificationPopup.vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
installQuasarPlugin()
describe("Nofitication Popup", () => {
  const wrapperFactory = (mocks = []) => {
    const mockClient = createMockClient()

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    })

    return {
      wrapper: mount(NotificationPopup, {
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
          type: "AppNotificationsSubmissionCreated",
          notifiable_type: "AppModelsUser",
          notifiable_id: 1,
          data: `"{"submission":{"id":9999,"title":"A Modest Proposal"},"publication":{"id":9999,"name":"Test Publication from Tinker"},"user":{"id":9999,"username":"Test User from Tinker"},"type":"submission.created","body":"A submission has been created.","action":"Visit CCR","url":"/"}"`,
          read_at: null,
          created_at: "2021-12-13 20:13:05",
          updated_at: "2021-12-13 20:13:05",
        },
      },
    ]
    if (readStatus === "read") {
      data[0]["read_at"] = "2021-12-31 12:15:15"
    }
    return data
  }

  it("mounts without errors", () => {
    const { mockClient } = wrapperFactory()
    const queryHandler = jest
      .fn()
      .mockResolvedValue(getNotificationData("unread"))
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    expect(wrapperFactory().wrapper).toBeTruthy()
  })

  it("displays an indicator for a user that has unread notifications", async () => {
    const { wrapper, mockClient } = wrapperFactory()
    const queryHandler = jest
      .fn()
      .mockResolvedValue(getNotificationData("unread"))
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    const indicator = wrapper.findComponent({ ref: "notification_indicator" })
    expect(indicator).toBeTruthy()
  })

  it("displays no indicator for a user that has no unread notifications", async () => {
    const { wrapper, mockClient } = wrapperFactory()
    const queryHandler = jest
      .fn()
      .mockResolvedValue(getNotificationData("read"))
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    expect(
      wrapper.findAllComponents({ ref: "notification_indicator" })
    ).toHaveLength(0)
  })

  it("displays no indicator for a user that has no notifications", async () => {
    const { wrapper, mockClient } = wrapperFactory()
    const queryHandler = jest.fn().mockResolvedValue([])
    mockClient.setRequestHandler(CURRENT_USER_NOTIFICATIONS, queryHandler)
    expect(
      wrapper.findAllComponents({ ref: "notification_indicator" })
    ).toHaveLength(0)
  })
})
