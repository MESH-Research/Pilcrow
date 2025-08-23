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
            name: 'submission:details',
            params: { id: submission.id }
          }"
        />
        <q-breadcrumbs-el :label="$t(`export.title`)" />
      </q-breadcrumbs>
    </nav>
    <article class="q-pa-lg">
      <h2 class="q-my-none">{{ $t(`export.title`) }}</h2>
      <h3>{{ submission.title }}</h3>
      <p>{{ $t(`export.description`) }}</p>
      <p>{{ $t(`export.download.description`) }}</p>
      <q-btn
        v-if="submission"
        class="q-mt-lg"
        :label="$t(`export.download.title`)"
        color="accent"
        icon="file_download"
        :href="blob"
        :download="`submission_${submission.id}.html`"
      />
      <q-spinner v-else />
    </article>
  </div>
</template>
<script setup lang="ts">
import { GET_SUBMISSION } from "src/graphql/queries"

definePage({
  name: "submission:export"
})

const { params } = useRoute("submission:export")

const submission = computed(() => {
  return result.value?.submission
})
const { result } = useQuery(GET_SUBMISSION, params)
const blob = computed(() =>
  URL.createObjectURL(
    new Blob([submission.value.content.data], { type: "text/html" })
  )
)
</script>
