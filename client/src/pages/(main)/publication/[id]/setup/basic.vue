<template>
  <article class="q-px-md">
    <h2>{{ $t("publication.setup_pages.basic") }}</h2>
    <UpdateBasicForm ref="form" :publication="publication" @save="save" />
  </article>
</template>

<script setup lang="ts">
import UpdateBasicForm from "src/components/forms/Publication/UpdateBasicForm.vue"
import type { PublicationSetupBasicFragment } from "src/gql/graphql"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import { useFormState } from "src/use/forms"

definePage({
  name: "publication:setup:basic",
  meta: {
    navigation: {
      icon: "tune",
      label: "Basic Setup"
    },
    crumb: {
      label: "Basic Setup",
      icon: "tune"
    }
  }
})

interface Props {
  publication: PublicationSetupBasicFragment
}
const props = defineProps<Props>()

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

<script lang="ts">
graphql(`
  fragment PublicationSetupBasic on Publication {
    id
    is_publicly_visible
    is_accepting_submissions
    name
  }
`)
</script>
