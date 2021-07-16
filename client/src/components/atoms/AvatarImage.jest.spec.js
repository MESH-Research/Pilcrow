import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import AvatarImage from "./AvatarImage.vue"

import * as All from "quasar"

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val.component?.name != null) {
    object[key] = val
  }
  return object
}, {})

describe("AvatarImage Component", () => {
  const factory = (email) => {
    return mountQuasar(AvatarImage, {
      quasar: { components },
      propsData: {
        user: {
          email,
        },
      },
      mount: {
        type: "shallow",
      },
    })
  }

  it("returns a deterministic value", () => {
    const wrapper = factory("test@ccrproject.dev")
    expect(wrapper.vm.avatarSrc).toBe("avatar-yellow.png")
  })
})
