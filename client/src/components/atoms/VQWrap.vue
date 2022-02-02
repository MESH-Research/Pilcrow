<template>
  <div>
    <slot />
  </div>
</template>

<script>
import { provide } from "vue"
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
  },
  emits: ["vqupdate"],
  setup(props, { emit }) {
    provide("tPrefix", props.tPrefix)
    provide("vqupdate", (validator, value) => {
      /**
       * VQInputs will emit their vqupdate events from this component instead of locally.
       */
      emit("vqupdate", validator, value)
    })
  },
}
</script>
