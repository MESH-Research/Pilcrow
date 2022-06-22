<template>
  <article class="q-px-md">
    <h2>General Settings</h2>
    <UpdateBasicForm ref="form" :publication="publication" @save="save" />
  </article>
</template>

<script setup>
import { useMutation } from "@vue/apollo-composable"
import { provide } from "vue"
import UpdateBasicForm from "src/components/forms/Publication/UpdateBasicForm.vue"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import { useFormState } from "src/use/forms"
const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})

const mutation = useMutation(UPDATE_PUBLICATION_BASICS)

const formState = useFormState(null, mutation)
provide("formState", formState)

function save(form) {
  const { saved, errorMessage } = formState
  saved.value = false
  mutation
    .mutate({ id: props.publication.id, ...form })
    .then(() => {
      saved.value = true
      errorMessage.value = ""
    })
    .catch(() => {
      saved.value = false
      errorMessage.value = "Unable to save publication"
    })
}
</script>
