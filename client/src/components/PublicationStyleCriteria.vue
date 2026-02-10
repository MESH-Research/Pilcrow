<template>
  <q-card flat>
    <q-card-section>
      <q-list separator>
        <q-item
          v-for="criteria in publication.style_criterias"
          :key="criteria.id"
          data-cy="listItem"
        >
          <component
            :is="editId === criteria.id ? StyleCriteriaForm : StyleCriteriaItem"
            class="criteria-card"
            :criteria="criteria"
            :edit-id="editId"
            data-cy="criteriaEditForm"
            @edit="editItem(criteria.id)"
            @cancel="cancelEdit"
            @save="saveEdit"
            @delete="onDelete"
          />
        </q-item>
        <q-item v-if="editId == ''">
          <style-criteria-form
            ref="addForm"
            @cancel="cancelEdit"
            @save="saveEdit"
          />
        </q-item>
      </q-list>
    </q-card-section>
    <q-card-actions v-if="editId === null" align="right">
      <q-btn
        ref="addBtn"
        data-cy="add-criteria-button"
        icon="add_task"
        :label="$t('publications.style_criteria.addBtnLabel')"
        color="accent"
        @click="newItem"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup lang="ts">
import { ref, toRef, provide, computed } from "vue"
import StyleCriteriaItem from "src/components/molecules/StyleCriteriaItem.vue"
import StyleCriteriaForm from "src/components/forms/StyleCriteriaForm.vue"
import { useFormState } from "src/use/forms"
import {
  UPDATE_PUBLICATION_STYLE_CRITERIA,
  CREATE_PUBLICATION_STYLE_CRITERIA,
  DELETE_PUBLICATION_STYLE_CRITERIA
} from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
const { t } = useI18n()

const editId = ref<string | null>(null)

const props = defineProps<{
  publication: Record<string, any>
}>()
const publication = toRef(props, "publication")

const variables = {
  publication_id: publication.value.id
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
const formState = useFormState(null, { loading })
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
