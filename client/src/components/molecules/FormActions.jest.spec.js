import FormActions from "./FormActions.vue"
import { createLocalVue } from "@vue/test-utils"
import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import * as All from "quasar"
import { nextTick } from "vue"
import compositionApi from "@vue/composition-api"

const localVue = createLocalVue()
localVue.use(compositionApi)

const components = Object.keys(All).reduce((object, key) => {
  const val = All[key]
  if (val && val.component && val.component.name != null) {
    object[key] = val
  }
  return object
}, {})

describe("Formactions", () => {
  const factory = (props = {}) => {
    return mountQuasar(FormActions, {
      localVue,
      quasar: { components },

      mount: {
        type: "full",
        mocks: {
          $t: (token) => token,
        },
        propsData: {
          ...props,
        },
      },
    })
  }
  test("wrapper is valid", async () => {
    const wrapper = factory()
    await nextTick()
    console.log(wrapper)
    expect(wrapper).toBeTruthy()
  })

  // test("hidden when form is idle", () => {
  //   const wrapper = factory({ formState: "idle" })
  //   //expect(wrapper.findComponent(QPageSticky).exists()).toBe(false)
  // })

  // test("dirty form state", async () => {
  //   const wrapper = factory({ formState: "dirty" })
  //   await nextTick()
  //   console.log(wrapper.vm.$children)
  //   //console.log(wrapper.findComponent(QPageSticky))
  //   //expect(wrapper.findComponent(QPageSticky).exists()).toBe(true)
  // })
})
