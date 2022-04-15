<template>
  <q-item>
    <q-item-section avatar>
      <q-icon :name="criteria.icon" />
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ criteria.name }}</q-item-label>
      <q-item-label caption>{{ criteria.description }}</q-item-label>
    </q-item-section>
    <q-item-section v-if="editId === null" side>
      <q-btn icon="edit" dense flat @click="$emit('edit')" />
    </q-item-section>
  </q-item>
</template>

<script setup>
defineProps({
  criteria: {
    type: Object,
    required: true,
  },
  editId: {
    type: String,
    default: null,
    required: false,
  },
})

defineEmits(["edit"])
</script>
