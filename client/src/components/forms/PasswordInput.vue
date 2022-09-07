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

<script>
import { defineComponent } from "vue"
export default defineComponent({
  inheritAttrs: false,
})
</script>

<script setup>
import { ref, useSlots } from "vue"
defineProps({
  label: {
    type: String,
    default: "Password",
  },
  modelValue: {
    type: String,
    default: "",
  },
  autocomplete: {
    default: "current-password",
    type: String,
  },
  outlined: {
    type: Boolean,
    default: false,
  },
  error: {
    type: [String, Boolean],
    default: false,
  },
})

defineEmits(["update:modelValue"])
const isPwd = ref(true)

const $slots = useSlots()
</script>
