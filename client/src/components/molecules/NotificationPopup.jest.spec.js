import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import NotificationPopup from "./NotificationPopup.vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

const notification_data = [
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

jest.mock("@vue/apollo-composable", () => {
  const { result } = notification_data
  return {
    useQuery: jest.fn(() => ({ result })),
  }
})

describe("Nofitication Popup", () => {
  const wrapper = mountQuasar(NotificationPopup, {
    quasar: {
      components,
    },
    mount: {
      type: "full",
      mocks: {
        $t: (token) => token,
      },
    },
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  it("displays an indicator for a user that has notifications", async () => {
    const indicator = wrapper.findComponent({ ref: "notiication_indicator" })
    expect(wrapper).toContain(indicator)
  })
})
