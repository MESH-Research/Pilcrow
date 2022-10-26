<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-if="status == 'success'" class="column flex-center">
        <q-icon color="positive" name="check_circle" size="2em" />
        <strong class="text-h3">{{
          $t("submissions.accept_invite.success.title")
        }}</strong>
        <p>{{ $t("submissions.accept_invite.success.message") }}</p>
      </div>
      <div v-if="status == 'error'" class="column flex-center">
        <q-icon color="negative" name="error" size="2em" />
        <strong class="text-h3">{{
          $t("submissions.accept_invite.failure.title")
        }}</strong>
        <p>{{ $t("submissions.accept_invite.failure.message") }}</p>
      </div>
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
    status.value = "error"
  }
})
</script>
