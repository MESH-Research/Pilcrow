<template>
  <div>
    <slot />
  </div>
</template>

<script>
import { provide, toRef } from "vue"
export default {
  props: {
    /**
     * TPrefix to provide to VQInput components. When supplied, the VQInput component will use
     * the validator $path property to determine the translation key.
     */
    tPrefix: {
      type: [String, Boolean],
      default: false,
    },
    /**
     * Provide formState to VQInput components to allow inputs to adapt to form states.
     * @values "Saved", "Saving", "Idle", "Dirty", "Error"
     */
    formState: {
      type: [String, Boolean],
      default: "",
    },
  },
  emits: ["vqupdate"],
  setup(props, { emit }) {
    provide("tPrefix", props.tPrefix)
    provide("formState", toRef(props, "formState"))
    provide("vqupdate", (validator, value) => {
      /**
       * VQInputs will emit their vqupdate events from this component instead of locally.
       */
      emit("vqupdate", validator, value)
    })
  },
}
</script>
