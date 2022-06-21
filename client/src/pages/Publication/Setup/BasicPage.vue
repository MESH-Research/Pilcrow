<template>
  <article class="q-pa-md">
    <h2>General Settings</h2>
    <UpdateBasicForm :publication="publication" />
  </article>
</template>

<script setup>
import { GET_PUBLICATION } from "src/graphql/queries"
import { useMutation, useQuery } from "@vue/apollo-composable"
import { computed, provide } from "vue"
import UpdateBasicForm from "src/components/forms/Publication/UpdateBasicForm.vue"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import { useFormState } from "src/use/forms"
const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => {
  return result.value?.publication ?? null
})
const mutation = useMutation(UPDATE_PUBLICATION_BASICS)

const formState = useFormState(null, mutation)
provide("formState", formState)
</script>
