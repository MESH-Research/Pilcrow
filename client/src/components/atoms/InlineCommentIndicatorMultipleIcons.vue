<template>
  <q-btn
    aria-label="Inline Comment # by User"
    v-bind="{ ...$attrs, ...$props }"
    size="md"
    :flat="isActive"
    dense
    color="secondary"
    style="margin-top: -3px"
    @click="clickHandler"
    ><q-icon v-for="icon in icons" :key="icon" :name="icon"
  /></q-btn>
</template>

<script setup>
defineProps({
  clickHandler: {
    type: Function,
    default: () => {},
  },
  icons: {
    type: Array,
    default: () => ["chat"],
  },
  isActive: {
    type: Boolean,
    default: false,
  },
})
</script>
