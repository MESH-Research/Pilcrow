<template>
  <MetaFormCreate
    v-if="creatingForm"
    :creating-form="creatingForm"
    :publication="props.publication"
    @cancel="cancelCreateForm"
    @submit="submitCreateForm"
  />
  <MetaFormEdit
    v-else-if="editingForm != null"
    :form="editingForm"
    @cancel="cancelEditForm"
    @submit="submitEditForm"
  />
  <article v-else class="q-pl-lg">
    <h2>Meta Forms</h2>
    <p>Customize blocks of text displayed to your publication's users.</p>
    <div v-if="loading">
      <q-spinner color="primary" />
    </div>
    <div v-else-if="result">
      <q-btn
        color="primary"
        icon="add"
        label="Add Meta Form"
        @click="createForm"
      />
      <q-list bordered separator class="q-mt-lg">
        <template v-for="form in metaForms" :key="form.id">
          <q-item :class="darkModeStatus ? `bg-blue-grey-10` : `bg-grey-3`">
            <q-item-section avatar>
              <q-icon name="ballot" />
            </q-item-section>
            <q-item-section>
              <q-item-label>
                {{ form.name }}
                <div v-if="form.caption">{{ form.caption }}</div>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-spinner v-if="form.loading.value" />
            </q-item-section>
            <q-item-section side>
              <q-btn
                flat
                icon="edit"
                color="accent"
                class="q-ml-xs"
                size="xs"
                padding="xs"
                aria-label="Change label"
                @click="editForm(form)"
              >
                <q-tooltip anchor="center left" self="center right">
                  Edit Form
                </q-tooltip>
              </q-btn>
            </q-item-section>
            <q-item-section side>
              <ChipRequired
                :required="form.required"
                :can-toggle="true"
                @click="toggleFormRequired(form)"
              />
            </q-item-section>
            <q-item-section side>
              <q-icon name="drag_handle" />
            </q-item-section>
          </q-item>
          <Draggable v-model="form.prompts.value" item-key="id" tag="QList">
            <template #item="{ element: prompt }">
              <q-item :inset-level="1">
                <q-item-section avatar>
                  <q-icon :name="promptIcon(prompt.type)" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{ prompt.label }}
                  </q-item-label>
                  <div v-if="prompt.caption">{{ prompt.caption }}</div>
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat
                    icon="edit"
                    color="accent"
                    class="q-ml-xs"
                    size="xs"
                    padding="xs"
                    aria-label="Change label"
                    @click="editForm(form)"
                  >
                    <q-tooltip anchor="center left" self="center right">
                      Edit Prompt
                    </q-tooltip>
                  </q-btn>
                </q-item-section>
                <q-item-section side>
                  <ChipRequired
                    :required="prompt.required"
                    :can-toggle="true"
                  />
                </q-item-section>
                <q-item-section side>
                  <q-icon name="drag_handle" />
                </q-item-section>
              </q-item>
            </template>
          </Draggable>
          <q-item>
            <q-item-section class="q-my-md" avatar>
              <q-btn color="primary" icon="add">Add Prompt</q-btn>
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </div>
  </article>
</template>

<script setup lang="ts">
import { GET_PUBLICATION_PROMPTS } from "src/graphql/queries"
import { useDarkMode } from "src/use/guiElements"
import ChipRequired from "src/components/atoms/ChipRequired.vue"
import Draggable from "vuedraggable"
definePage({
  name: "publication:setup:metaForms",
  meta: {
    navigation: {
      icon: "ballot",
      label: "Meta Forms"
    }
  }
})

const { darkModeStatus } = useDarkMode()
const editingForm = ref(null)
const creatingForm = ref(false)

const props = defineProps({
  publication: {
    type: Object,
    required: true
  }
})

const { result, loading } = useQuery(GET_PUBLICATION_PROMPTS, {
  id: props.publication.id
})

const { mutate: updateMetaPrompts } = useMutation(META_PROMPT_UPDATE)
const { mutate: updateMetaForm } = useMutation(META_FORM_UPDATE)

function createForm() {
  creatingForm.value = true
}
function submitCreateForm() {
  creatingForm.value = false
}
function cancelCreateForm() {
  creatingForm.value = false
}
function editForm(form) {
  editingForm.value = form
}
function cancelEditForm() {
  editingForm.value = null
}
function submitEditForm() {
  editingForm.value = null
}

function toggleFormRequired(form) {
  const newForm = { id: form.id, required: !form.required }
  updateMetaForm({ input: { newForm } })
    .catch((error) => {
      console.error(error)
    })
    .finally(() => (loading.value = false))
}

const metaForms = computed(() => {
  return (
    result.value?.publication.meta_forms?.map((form) => {
      const loading = ref(false)
      return {
        ...form,
        loading,
        prompts: computed({
          get: () => form.meta_prompts.toSorted((a, b) => a.order - b.order),
          set: (newPrompts) => {
            let index = 0
            const order = newPrompts.map((p) => ({
              id: p.id,
              order: index++
            }))

            loading.value = true
            updateMetaPrompts({ input: order })
              .catch((error) => {
                console.error("Error updating meta prompts:", error)
                //TODO: show error to the user
              })
              .finally(() => (loading.value = false))
          }
        })
      }
    }) ?? []
  )
})

function promptIcon(type) {
  switch (type) {
    case "INPUT":
      return "text_fields"
    case "SELECT":
      return "list"
    case "CHECKBOX":
      return "check_box"
    case "TEXTAREA":
      return "short_text"
    default:
      return "help"
  }
}
</script>

<script lang="ts">
import { useMutation, useQuery } from "@vue/apollo-composable"
import gql from "graphql-tag"
import MetaFormCreate from "src/components/forms/MetaFormCreate.vue"
import MetaFormEdit from "src/components/forms/MetaFormEdit.vue"

const META_PROMPT_UPDATE = gql`
  mutation MetaPromptUpdate($input: [UpdateMetaPromptInput!]!) {
    metaPromptUpdate(input: $input) {
      id
      label
      order
      required
      type
      options
      caption
    }
  }
`

const META_FORM_UPDATE = gql`
  mutation MetaFormUpdate($input: [UpdateMetaFormInput!]!) {
    metaFormUpdate(input: $input) {
      id
      name
      caption
      required
      order
    }
  }
`
</script>
