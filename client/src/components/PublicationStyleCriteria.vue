<template>
  <q-card flat class="q-ma-none">
    <q-card-section>
      <div class="text-h3">{{ $t("publications.style_criteria.heading") }}</div>
    </q-card-section>
    <q-card-section>
      <q-list bordered separator>
        <component
          :is="editId === criteria.id ? StyleCriteriaForm : StyleCriteriaItem"
          v-for="criteria in publication.style_criterias"
          :key="criteria.id"
          data-cy="listItem"
          :criteria="criteria"
          :edit-id="editId"
          @edit="editItem(criteria.id)"
          @cancel="cancelEdit"
          @save="saveEdit"
        />
        <style-criteria-form
          v-if="editId == ''"
          ref="addForm"
          @cancel="cancelEdit"
          @save="saveEdit"
        />
      </q-list>
    </q-card-section>
    <q-card-actions v-if="editId === null" align="right">
      <q-btn
        ref="addBtn"
        data-cy="add-criteria-button"
        icon="add_task"
        label="Add Criteria"
        flat
        @click="newItem"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { ref, toRef, provide, computed } from "vue"
import StyleCriteriaItem from "src/components/molecules/StyleCriteriaItem.vue"
import StyleCriteriaForm from "src/components/forms/StyleCriteriaForm.vue"
import { useFormState } from "src/use/forms"
import {
  UPDATE_PUBLICATION_STYLE_CRITERIA,
  CREATE_PUBLICATION_STYLE_CRITERIA,
} from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
const editId = ref(null)

const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
const publication = toRef(props, "publication")

const variables = {
  publication_id: publication.value.id,
}
const { updateLoading, mutate: updateCriteria } = useMutation(
  UPDATE_PUBLICATION_STYLE_CRITERIA,
  { variables }
)
const { createLoading, mutate: createCriteria } = useMutation(
  CREATE_PUBLICATION_STYLE_CRITERIA,
  { variables }
)
const loading = computed(() => updateLoading || createLoading)
const formState = useFormState(ref(false), loading)
provide("formState", formState)

function editItem(criteriaId) {
  if (editId.value !== null) return

  editId.value = criteriaId
}

function cancelEdit() {
  editId.value = null
  formState.reset()
}

function newItem() {
  editId.value = ""
}

async function saveEdit(criteria) {
  try {
    const method = criteria.id === "" ? createCriteria : updateCriteria
    await method(criteria)
    formState.reset()
    editId.value = null
  } catch (error) {
    formState.errorMessage.value = "Unable to save.  Check form for errors."
  }
}
</script>
