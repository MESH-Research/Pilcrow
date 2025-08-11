<template>
  <article class="q-pl-lg">
    <h2>Meta Forms</h2>
    <p>Customize blocks of text displayed to your publication's users.</p>
    <div v-if="loading">
      <q-spinner color="primary" />
    </div>
    <div v-else-if="result">
      <q-btn
        :to="{ name: 'publication:setup:metaFormCreate' }"
        color="primary"
        icon="add"
        label="Add Meta Form"
      />
      <q-list bordered separator class="q-mt-lg">
        <template v-for="set in metaForms" :key="set.id">
          <q-item :class="darkModeStatus ? `bg-blue-grey-10` : `bg-grey-3`">
            <q-item-section avatar>
              <q-icon name="ballot" />
            </q-item-section>
            <q-item-section>
              <q-item-label>
                {{ set.name }}

                <div v-if="set.caption">{{ set.caption }}</div>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-spinner v-if="set.loading.value" />
            </q-item-section>
            <q-item-section side>
              <q-btn
                v-if="!editing_title"
                flat
                icon="edit"
                color="accent"
                class="q-ml-xs"
                size="xs"
                padding="xs"
                aria-label="Change label"
                @click="editTitle"
              >
                <q-tooltip anchor="center left" self="center right">
                  Edit Form
                </q-tooltip>
              </q-btn>
            </q-item-section>
            <q-item-section side>
              <ChipRequired :required="set.required" :can-toggle="true" />
            </q-item-section>
            <q-item-section side>
              <q-icon name="drag_handle" />
            </q-item-section>
          </q-item>
          <Draggable v-model="set.prompts.value" item-key="id" tag="QList">
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
                    v-if="!editing_title"
                    flat
                    icon="edit"
                    color="accent"
                    class="q-ml-xs"
                    size="xs"
                    padding="xs"
                    aria-label="Change label"
                    @click="editTitle"
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

<script setup>
import { computed, ref } from "vue"

import Draggable from "vuedraggable"

import { useDarkMode } from "src/use/guiElements"

const { darkModeStatus } = useDarkMode()

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

<script>
import { useMutation, useQuery } from "@vue/apollo-composable"
import gql from "graphql-tag"
import ChipRequired from "src/components/atoms/ChipRequired.vue"

const GET_PUBLICATION_PROMPTS = gql`
  query GetPublicationPrompts($id: ID!) {
    publication(id: $id) {
      id
      meta_forms {
        id
        name
        caption
        required
        meta_prompts {
          id
          label
          type
          order
          options
          required
          caption
        }
      }
    }
  }
`

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
</script>
