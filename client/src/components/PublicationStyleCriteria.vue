<template>
  <q-card flat bordered>
    <q-card-section>
      <div class="text-h3">Style Critiera</div>
    </q-card-section>
    <q-card-section>
      <q-list>
        <component
          :is="editId === criteria.id ? StyleCriteriaForm : StyleCriteriaItem"
          v-for="criteria in publication.style_criterias"
          :key="criteria.id"
          :criteria="criteria"
          :edit-id="editId"
          @edit="editItem(criteria.id)"
          @cancel="cancelEdit"
        />
      </q-list>
    </q-card-section>
    <q-card-section v-if="editId == ''">
      <style-criteria-form @cancel="cancelEdit" />
    </q-card-section>
    <q-card-actions v-else align="right">
      <q-btn icon="add_task" label="Add Criteria" flat @click="newItem" />
    </q-card-actions>

    <q-separator v-if="editMode" />
    <q-card-section v-if="editMode"> </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, toRef } from "vue"
import StyleCriteriaItem from "src/components/molecules/StyleCriteriaItem.vue"
import StyleCriteriaForm from "./forms/StyleCriteriaForm.vue"

const editId = ref(null)

const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})

function editItem(criteriaId) {
  if (editId.value !== null) return

  editId.value = criteriaId
}

function cancelEdit() {
  editId.value = null
}

function newItem() {
  editId.value = ""
}

const publication = toRef(props, "publication")
</script>
