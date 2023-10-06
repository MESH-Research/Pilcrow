<template>
  <div data-cy="submission_review_page">
    <div v-if="loading" class="q-pa-lg">
      {{ $t("loading") }}
    </div>
    <div
      v-else-if="!submission?.content && submission?.status === 'DRAFT'"
      class="q-pa-xl items-center column content-center text-center"
    >
      <p style="max-width: 20rem" data-cy="explanation">
        {{ $t('submissions.draft_with_no_content') }}
      </p>
      <q-btn
        data-cy="draft_btn"
        :label="$t(`submissions.action.draft`)"
        color="primary"
        class="q-mt-md"
        :to="{
          name: 'submission:draft',
          params: { id: submission.id },
        }"
      />
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
          v-model:highlightVisibility="highlightVisibility"
          :submission="submission"
        />
        <submission-comment-drawer v-model:drawerOpen="commentDrawerOpen" />
        <q-page-container>
          <submission-content
            v-model:highlightVisibility="highlightVisibility"
            @scroll-to-overall-comments="handleScroll"
          />
          <q-separator class="page-seperator" />
          <div ref="scrollOverallComments"></div>
          <submission-comment-section/>
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
import { scroll } from "quasar"
const { getScrollTarget, setVerticalScrollPosition } = scroll

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { loading, result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
provide("submission", submission)
provide("activeComment", ref(null))
provide("commentDrawerOpen", commentDrawerOpen)

const scrollOverallComments = ref(null)

function handleScroll() {
    const Svalue = scrollOverallComments.value
    const Starget = getScrollTarget(Svalue)
    console.log(Svalue, Starget)
  setVerticalScrollPosition(Starget, Svalue.offsetTop, 250)
}

</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>
