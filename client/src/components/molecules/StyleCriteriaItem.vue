<template>
  <q-item class="q-pa-md">
    <q-item-section top avatar style="min-width: auto">
      <q-icon size="md" :name="criteria.icon" />
    </q-item-section>
    <q-item-section>
      <q-item-label>{{ criteria.name }}</q-item-label>
      <!-- eslint-disable-next-line vue/no-v-html -->
      <q-item-label caption v-html="criteria.description" />
    </q-item-section>
    <q-item-section v-if="editId === null" side>
      <q-btn
        data-cy="editBtn"
        icon="edit"
        dense
        flat
        aria-label="$t('publication.style_critiera.edit_button')"
        @click="$emit('edit')"
      />
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
