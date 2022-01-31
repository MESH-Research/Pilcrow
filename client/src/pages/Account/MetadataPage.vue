<template>
  <profile-metadata-form :profile-metadata="profileMetadata" @save="save" />
</template>

<script setup>
//Import components
import ProfileMetadataForm from "src/components/forms/ProfileMetadataForm.vue"
import { provide } from "vue"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { CURRENT_USER_METADATA } from "src/graphql/queries"
import { UPDATE_PROFILE_METADATA } from "src/graphql/mutations"
import { useFormState } from "src/use/forms"

const metadataQuery = useQuery(CURRENT_USER_METADATA)
const metadataMutation = useMutation(UPDATE_PROFILE_METADATA)

const formState = useFormState(metadataQuery.loading, metadataMutation.loading)

provide("formState", formState)

const profileMetadata = useResult(
  metadataQuery.result,
  {},
  (data) => data.currentUser.profile_metadata
)

const currentUserId = useResult(
  metadataQuery.result,
  {},
  (data) => data.currentUser.id
)

const { mutate: saveProfile } = metadataMutation

function save(form) {
  const { saved, errorMessage } = formState
  saved.value = false
  saveProfile({ id: currentUserId.value, ...form })
    .then(() => {
      saved.value = true
      errorMessage.value = ""
    })
    .catch(() => {
      saved.value = false
      errorMessage.value = "Unable to save profile."
    })
}
</script>
