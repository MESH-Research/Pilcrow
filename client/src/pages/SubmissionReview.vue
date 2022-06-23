<template>
  <div data-cy="submission_review_page">
    <div v-if="!submission" class="q-pa-lg">
      {{ $t("loading") }}
    </div>
    <article v-else>
      <q-layout
        data-cy="submission_review_layout"
        view="hHh lpR fFr"
        container
        style="min-height: calc(100vh - 70px)"
      >
        <submission-toolbar
          :id="id"
          v-model:commentDrawerOpen="commentDrawerOpen"
          :submission="submission"
        />
        <submission-comment-drawer v-model:drawerOpen="commentDrawerOpen" />
        <q-page-container>
          <submission-comment-section />
          <q-separator class="page-seperator" />
          <submission-content />
        </q-page-container>
      </q-layout>

      <div class="row q-col-gutter-lg q-pa-lg"></div>
    </article>
  </div>
</template>

<script setup>
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import SubmissionToolbar from "src/components/atoms/SubmissionToolbar.vue"
import { ref, provide, computed } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
const commentDrawerOpen = ref(false)
provide("submission", submission)
provide("activeComment", ref(null))
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>
