import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import NotificationsPopup from "./NotificationsPopup.vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

describe("Nofitications Popup", () => {
  mountQuasar(NotificationsPopup, {
    quasar: {
      components,
    },
    mount: {
      type: "full",
      stubs: ["router-link"],
      mocks: {
        currentPage: 1,
      },
    },
  })

  it("displays an indicator for a user that has notifications", () => {
    //
  })

  it("displays a user's notifications", () => {
    //
  })
})
