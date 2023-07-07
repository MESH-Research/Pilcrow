<template>
  <article class="q-px-md">
    <h2>Page Content</h2>
    <p>Customize blocks of text displayed to your publication's users.</p>
    <UpdateContentForm ref="form" :publication="publication" @save="save" />
  </article>
</template>

<script setup>
import { useMutation } from "@vue/apollo-composable"
import UpdateContentForm from "src/components/forms/Publication/UpdateContentForm.vue"
import { useFormState } from "src/use/forms"
import { provide } from "vue"
import { UPDATE_PUBLICATION_CONTENT } from "src/graphql/mutations"
const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})

const mutation = useMutation(UPDATE_PUBLICATION_CONTENT)
const formState = useFormState(null, mutation)
provide("formState", formState)

function save(form) {
  const { saved, errorMessage } = formState
  const vars = { id: props.publication.id }
  vars[form.field] = form.content
  saved.value = false
  mutation
    .mutate(vars)
    .then(() => {
      saved.value = true
      errorMessage.value = ""
    })
    .catch(() => {
      saved.value = false
      errorMessage.value = "Unable to save publication."
    })
}
</script>
