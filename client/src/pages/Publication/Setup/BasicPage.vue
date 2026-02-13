<template>
  <article class="q-px-md">
    <h2>{{ $t("publication.setup_pages.basic") }}</h2>
    <UpdateBasicForm ref="form" :publication="publication" @save="save" />
  </article>
</template>

<script setup lang="ts">
import { useMutation } from "@vue/apollo-composable"
import { provide } from "vue"
import UpdateBasicForm from "src/components/forms/Publication/UpdateBasicForm.vue"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import { useFormState, formStateKey } from "src/use/forms"
import type { Publication } from "src/graphql/generated/graphql"
const props = defineProps<{
  publication: Publication
}>()

const mutation = useMutation(UPDATE_PUBLICATION_BASICS)

const formState = useFormState(null, mutation)
provide(formStateKey, formState)

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
