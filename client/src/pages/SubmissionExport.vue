<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-pa-lg">
    <h2 class="q-my-none">Export</h2>
    <h3>{{ submission.title }}</h3>
    <p>
      This page allows you to export this submission as a HTML file. This
      submission's file contents will download automatically to your device.
    </p>
    <q-btn label="Download" color="accent" @click="crap()"/>
  </article>
</template>
<script setup>
import { computed } from "vue"
import { GET_SUBMISSION} from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
const { result } = useQuery(GET_SUBMISSION, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
</script>
