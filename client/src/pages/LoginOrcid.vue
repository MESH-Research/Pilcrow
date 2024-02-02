<template>
  <q-page>
    <section class="q-pa-lg">
      <div class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { onMounted } from "vue"
import { useRoute } from "vue-router"
import { useMutation } from "@vue/apollo-composable"
import { LOGIN_ORCID_CALLBACK } from "src/graphql/mutations"
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_ORCID_CALLBACK, {
  variables: { code: code },
})

onMounted(async () => {
  try {
    const response = await handleCallback()
    console.log(response)
  } catch (error) {
    console.error(error)
  }
})
</script>
