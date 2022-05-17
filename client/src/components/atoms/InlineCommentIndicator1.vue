<template>
  <q-btn
    v-bind="{ ...$attrs, ...$props }"
    size="md"
    :flat="isActive"
    dense
    :color="isActive ? `secondary` : `dark`"
    :icon="iconName"
    style="margin-top: -3px"
    @click="clickHandler"
  ></q-btn>
</template>

<script setup>
defineProps({
  clickHandler: {
    type: Function,
    default: () => {},
  },
  iconName: {
    type: String,
    default: "chat",
  },
  isActive: {
    type: Boolean,
    default: false,
  },
})
</script>
