<template>
  <q-btn
    :aria-label="props.ariaLabel"
    color="black"
    outline
    dense
    size="sm"
    :class="{ 'is-active': props.isActive }"
    :disabled="props.isDisabled"
    @click="props.clickHandler"
  >
    <q-tooltip class="bg-primary">{{ props.tooltipText }}</q-tooltip>
    <q-icon :name="props.iconName"></q-icon>
  </q-btn>
</template>

<script setup>
const props = defineProps({
  ariaLabel: {
    type: String,
    default: "",
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  isDisabled: {
    type: Boolean,
    default: false,
  },
  clickHandler: {
    type: Function,
    default: () => {},
  },
  tooltipText: {
    type: String,
    default: "",
  },
  iconName: {
    type: String,
    default: "",
  },
})
</script>
