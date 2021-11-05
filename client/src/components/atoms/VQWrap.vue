<template>
  <div>
    <slot />
  </div>
</template>

<script>
import { provide, toRef } from "@vue/composition-api"
export default {
  props: {
    tPrefix: {
      type: [String, Boolean],
      default: false,
    },
    formState: {
      type: [String, Boolean],
      default: "",
    },
  },
  setup(props, { emit }) {
    provide("tPrefix", props.tPrefix)
    provide("formState", toRef(props, "formState"))
    provide("vqupdate", (validator, value) => {
      emit("vqupdate", validator, value)
    })
  },
}
</script>
