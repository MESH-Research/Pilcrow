<template>
  <email-verification-banner v-if="!emailVerified" />
  <router-view v-if="!loading" />
</template>

<script setup lang="ts">
import EmailVerificationBanner from "src/components/molecules/EmailVerificationBanner.vue"
import { MainLayoutDocument } from "src/gql/graphql"

import { useCurrentUser } from "src/use/user"

const { currentUser } = useCurrentUser()

const { result, loading } = useQuery(MainLayoutDocument)
const { push } = useRouter()

const emailVerified = computed(() => !!currentUser.value?.email_verified_at)
watchEffect(() => {
  if (result.value && !loading.value && !currentUser.value) {
    void push({ name: "index" })
  }
})
</script>

<script lang="ts">
graphql(`
  query MainLayout {
    currentUser {
      id
      email_verified_at
    }
  }
`)
</script>
