<template>
  <q-card flat>
    <q-card-section>
      <q-list separator>
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
          @delete="onDelete"
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
        :label="$t('publications.style_criteria.addBtnLabel')"
        color="primary"
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
  DELETE_PUBLICATION_STYLE_CRITERIA,
} from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
const { t } = useI18n()

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
const { loading: updateLoading, mutate: updateCriteria } = useMutation(
  UPDATE_PUBLICATION_STYLE_CRITERIA,
  { variables }
)
const { loading: createLoading, mutate: createCriteria } = useMutation(
  CREATE_PUBLICATION_STYLE_CRITERIA,
  { variables }
)

const { loading: deleteLoading, mutate: deleteCriteria } = useMutation(
  DELETE_PUBLICATION_STYLE_CRITERIA,
  { variables }
)
const loading = computed(
  () => updateLoading.value || createLoading.value || deleteLoading.value
)
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

async function onDelete(criteria) {
  try {
    await deleteCriteria({ id: criteria.id })
    formState.reset()
    editId.value = null
  } catch (error) {
    formState.errorMessage.value = t("publications.style_criteria.deleteError")
  }
}

async function saveEdit(criteria) {
  try {
    const method = criteria.id === "" ? createCriteria : updateCriteria
    await method(criteria)
    formState.reset()
    editId.value = null
  } catch (error) {
    formState.errorMessage.value = t("publications.style_criteria.saveError")
  }
}
</script>
