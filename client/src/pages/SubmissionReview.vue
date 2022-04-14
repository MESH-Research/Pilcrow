<template>
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
        v-model="commentDrawerOpen"
        :submission="submission"
      />
      <submission-comment-drawer
        :comment-drawer-open="commentDrawerOpen"
        :submission="submission"
      />
      <q-page-container>
        <submission-content />
        <q-separator class="page-seperator" />
        <submission-comment-section />
      </q-page-container>
    </q-layout>

    <div class="row q-col-gutter-lg q-pa-lg"></div>
  </article>
</template>

<script setup>
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import SubmissionToolbar from "src/components/atoms/SubmissionToolbar.vue"
import { ref } from "vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const submission = useResult(useQuery(GET_SUBMISSION, { id: props.id }).result)
const commentDrawerOpen = ref(true)
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>
