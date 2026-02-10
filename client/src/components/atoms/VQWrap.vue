<template>
  <div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { provide } from "vue"

const props = withDefaults(
  defineProps<{
    tPrefix?: string | boolean
  }>(),
  {
    tPrefix: false
  }
)

const emit = defineEmits<{
  vqupdate: [validator: any, value: any]
}>()

provide("tPrefix", props.tPrefix)
provide("vqupdate", (validator: any, value: any) => {
  emit("vqupdate", validator, value)
})
</script>
