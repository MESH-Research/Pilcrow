<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <div v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el
          :label="$t('submissions.details_heading')"
          :to="{
            name: 'submission_details',
            params: { id: submission.id },
          }"
        />
        <q-breadcrumbs-el label="Export" />
      </q-breadcrumbs>
    </nav>
    <article class="q-pa-lg">
      <h2 class="q-my-none">Export</h2>
      <h3>{{ submission.title }}</h3>
      <p>This page allows you to export this submission as HTML.</p>
      <p>
        The file contents will download directly to your
        device in this format.
      </p>
      <q-btn
        class="q-mt-lg"
        label="Download"
        color="accent"
        icon="file_download"
        @click="download()"
      />
    </article>
  </div>
</template>
<script setup>
import { computed } from "vue"
import { GET_SUBMISSION } from "src/graphql/queries"
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
function download() {
  console.log("download")
  console.log(submission.value.content.data)
}
</script>
