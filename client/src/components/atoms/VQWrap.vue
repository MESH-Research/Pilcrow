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

interface Emits {
  vqupdate: [validator: VuelidateValidator, value: string]
}

const emit = defineEmits<Emits>()

provide("tPrefix", props.tPrefix)
provide("vqupdate", (validator: VuelidateValidator, value: string) => {
  emit("vqupdate", validator, value)
})
</script>
