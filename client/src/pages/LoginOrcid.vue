<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-else-if="status == 'register'">
        <p>Time to register</p>
      </div>
      <div v-else-if="status == 'auth'">
        <p>Time to auth</p>
      </div>
      <div v-else>
        <p>Whoops</p>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import { useMutation } from "@vue/apollo-composable"
import { LOGIN_ORCID_CALLBACK } from "src/graphql/mutations"
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_ORCID_CALLBACK, {
  variables: { code: code },
})

const status = ref("loading")

onMounted(async () => {
  try {
    const response = await handleCallback()
    status.value = response.data.loginOrcidCallback.status
    console.log(response, response.data.loginOrcidCallback.register)
  } catch (error) {
    console.error(error)
  }
})
</script>
