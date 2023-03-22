<template>
  <q-banner class="bg-grey-3" inline-actions>
    <div>{{ title }}</div>
    <div class="text-caption">
      <slot />
    </div>
    <template #action>
      <q-btn flat @click="$emit('goClick')">Go</q-btn>
      <q-btn v-if="$props.skipable" flat @click="$emit('skipClick')">
        Skip
      </q-btn>
    </template>
  </q-banner>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  skipable: {
    type: Boolean,
    required: false,
    default: false,
  },
  done: {
    type: Boolean,
    required: false,
    default: false,
  },
})
defineEmits(["goClick", "skipClick"])
</script>

<style scoped></style>
