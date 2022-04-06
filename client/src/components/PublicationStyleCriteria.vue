<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="text-h3">Style Critiera</div>
    </q-card-section>
    <q-card-section>
      <q-list>
        <q-item
          v-for="criteria in publication.style_criterias"
          :key="criteria.id"
        >
          <q-item-section avatar>
            <q-icon :name="criteria.icon" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ criteria.name }}</q-item-label>
            <q-item-label caption>{{ criteria.description }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>
    <q-card-actions align="right">
      <q-btn :label="editMode ? 'Done' : 'Edit'" flat @click="changeEditMode" />
    </q-card-actions>
    <q-separator v-if="editMode" />
    <q-card-section v-if="editMode"> </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, toRef } from "vue"

const editMode = ref(false)

const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})

const publication = toRef(props, "publication")
function changeEditMode() {
  editMode.value = !editMode.value
}
</script>

<style></style>
