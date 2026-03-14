<template>
  <q-input
    v-bind="{ ...$props, ...$attrs }"
    :model-value="$props.modelValue"
    :label="label"
    :type="isPwd ? 'password' : 'text'"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #append>
      <q-icon
        :name="isPwd ? 'visibility_off' : 'visibility'"
        class="cursor-pointer"
        color="accent"
        aria-hidden="false"
        role="button"
        tabindex="0"
        :aria-pressed="(!isPwd).toString()"
        :aria-label="$t('auth.aria.show_password')"
        @click="isPwd = !isPwd"
        @keydown.enter.space="isPwd = !isPwd"
      />
    </template>
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot :name="name" v-bind="{ ...slotData }" />
    </template>
  </q-input>
</template>

<script lang="ts">
import { defineComponent } from "vue"
export default defineComponent({
  inheritAttrs: false
})
</script>

<script setup lang="ts">
import { ref, useSlots } from "vue"

interface Props {
  label?: string
  modelValue?: string
  autocomplete?: string
  outlined?: boolean
  error?: boolean
}

withDefaults(defineProps<Props>(), {
  label: "Password",
  modelValue: "",
  autocomplete: "current-password",
  outlined: false,
  error: false
})

interface Emits {
  "update:modelValue": [value: string | number | null]
}

defineEmits<Emits>()

const isPwd = ref(true)

const $slots = useSlots()
</script>
