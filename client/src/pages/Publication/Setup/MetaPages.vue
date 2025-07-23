<template>
  <article class="q-px-md">
    <h2>Meta Pages</h2>
    <p>Customize blocks of text displayed to your publication's users.</p>
    <div v-if="loading">Loading...</div>
    <div v-else-if="result">
      <q-list bordered separator class="q-mb-lg">
        <template v-for="set in metaPages" :key="set.id">
          <q-item :class="darkModeStatus ? `bg-blue-grey-10` : `bg-grey-3`">
            <q-item-section avatar>
              <q-icon name="ballot" />
            </q-item-section>
            <q-item-section>
              <q-item-label>
                {{ set.name }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-spinner v-if="set.loading.value" />
            </q-item-section>
            <q-item-section side>
              <q-chip v-if="set.required" class="bg-secondary text-white"
                >Required</q-chip
              >
            </q-item-section>
            <q-item-section side>
              <q-icon name="drag_handle" />
            </q-item-section>
          </q-item>
          <Draggable v-model="set.questions.value" item-key="id" tag="QList">
            <template #item="{ element: question }">
              <q-item :inset-level="1">
                <q-item-section avatar>
                  <q-icon :name="questionIcon(question.type)" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{ question.label }}
                  </q-item-label>
                  <div v-if="question.caption">{{ question.caption }}</div>
                </q-item-section>
                <q-item-section side>
                  <q-chip
                    v-if="question.required"
                    class="bg-secondary text-white"
                  >
                    Required
                  </q-chip>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="drag_handle" />
                </q-item-section>
              </q-item>
            </template>
          </Draggable>
          <q-item>
            <q-item-section class="q-my-md">
              <q-btn color="primary" icon="add">Add Question</q-btn>
            </q-item-section>
          </q-item>
        </template>
      </q-list>
      <q-btn color="primary" icon="add" label="Add Question Set" />
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

const metaPages = computed(() => {
  return (
    result.value?.publication.meta_pages?.map((set) => {
      const loading = ref(false)
      return {
        ...set,
        loading,
        questions: computed({
          get: () => set.meta_prompts,
          set: (newQuestions) => {
            const order = newQuestions.map((q) => q.id)
            loading.value = true
            console.log("Setting new questions for set: ", set.id, order)
          }
        })
      }
    }) ?? []
  )
})

function questionIcon(type) {
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
import { useQuery } from "@vue/apollo-composable"
import gql from "graphql-tag"

const GET_PUBLICATION_PROMPTS = gql`
  query GetPublicationPrompts($id: ID!) {
    publication(id: $id) {
      id
      meta_pages {
        id
        name
        required
        meta_prompts {
          id
          label
          type
          options
          required
          caption
        }
      }
    }
  }
`
</script>
