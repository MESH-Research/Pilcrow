<template>
  <h1 class="text-h2 q-pl-md" data-cy="page_heading">
    {{ $t("account.profile.section_profile") }}
  </h1>
  <div v-if="currentUser" class="q-px-md q-mb-lg">
    <avatar-uploader :user="currentUser" />
  </div>
  <profile-metadata-form
    ref="form"
    :profile-metadata="profileMetadata"
    @save="save"
  />
</template>

<script setup lang="ts">
import ProfileMetadataForm from "src/components/forms/ProfileMetadataForm.vue"
import AvatarUploader from "src/components/molecules/AvatarUploader.vue"
import type { ProfileFormData } from "src/use/profileMetadata"
import { computed, provide } from "vue"
import { useMutation, useQuery } from "@vue/apollo-composable"
import { CURRENT_USER_METADATA } from "src/graphql/queries"
import { UPDATE_PROFILE_METADATA } from "src/graphql/mutations"
import { useFormState, formStateKey } from "src/use/forms"
import { useCurrentUser } from "src/use/user"

definePage({
  name: "account:profile",
  meta: {
    navigation: {
      icon: "account_circle",
      label: "profile.page_title",
      order: 10
    }
  }
})

const { currentUser } = useCurrentUser()

const metadataQuery = useQuery(CURRENT_USER_METADATA)
const metadataMutation = useMutation(UPDATE_PROFILE_METADATA)

const formState = useFormState(metadataQuery, metadataMutation)
provide(formStateKey, formState)

const profileMetadata = computed(() => {
  return metadataQuery.result.value?.currentUser ?? {}
})

const currentUserId = computed(() => {
  return metadataQuery.result.value?.currentUser.id
})

const { mutate: saveProfile } = metadataMutation

function save(form: ProfileFormData) {
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
