<template>
  <q-btn
    color="black"
    outline
    dense
    size="sm"
    :class="{ 'is-active': props.isActive }"
    :disabled="props.isDisabled"
    @click="props.clickHandler"
  >
    <q-tooltip
      class="bg-primary"
      anchor="top middle"
      self="bottom middle"
      :offset="[0, 5]"
      >{{ props.tooltipText }}</q-tooltip
    >
    <q-icon :name="props.iconName"></q-icon>
  </q-btn>
</template>

<script setup lang="ts">
interface Props {
  isActive?: boolean
  isDisabled?: boolean
  clickHandler?: (...args: unknown[]) => void
  tooltipText?: string
  iconName?: string
}
const props = withDefaults(defineProps<Props>(), {
  isActive: false,
  isDisabled: false,
  clickHandler: () => {},
  tooltipText: "",
  iconName: ""
})
</script>
