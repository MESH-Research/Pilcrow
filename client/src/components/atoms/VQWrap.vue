<template>
  <div>
    <slot />
  </div>
</template>

<script setup lang="ts">
import { provide } from "vue"
import type { VuelidateValidator } from "src/types/vuelidate"

interface Props {
  tPrefix?: string | boolean
}

const props = withDefaults(defineProps<Props>(), {
  tPrefix: false
})

const emit = defineEmits<{
  vqupdate: [validator: VuelidateValidator, value: string]
}>()

provide("tPrefix", props.tPrefix)
provide("vqupdate", (validator: VuelidateValidator, value: string) => {
  emit("vqupdate", validator, value)
})
</script>
