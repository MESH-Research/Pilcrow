<template>
  <q-page>
    <section v-if="status == 'loading'">
      <q-spinner-hourglass size="3em" />
    </section>
    <section v-if="status == 'success'">
      <h1>Success</h1>
      <q-icon color="positive" name="check_circle" />
    </section>
    <section v-if="status == 'error'">
      <h1>Error</h1>
      <q-icon color="negative" name="error" />
    </section>
  </q-page>
</template>

<script setup>
import { ACCEPT_SUBMISSION_INVITE } from "src/graphql/mutations"
import { ref, onMounted } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useRoute } from "vue-router"

const status = ref("loading")

const { mutate: invite } = useMutation(ACCEPT_SUBMISSION_INVITE)
const { params } = useRoute()

onMounted(async () => {
  const { token } = params

  try {
    await invite({ token })
    status.value = "success"
  } catch (error) {
    console.log(error)
    status.value = "error"
  }
})
</script>
