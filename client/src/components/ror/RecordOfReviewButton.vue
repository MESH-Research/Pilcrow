<template>
  <div v-if="canViewRecordOfReview">
    <q-btn
      :label="$t('record_of_review.title')"
      color="accent"
      icon="exit_to_app"
      :to="{ name: 'account:record_of_review' }"
    />
  </div>
</template>
<script setup lang="ts">
import { useCurrentUser } from "src/use/user"
import { computed } from "vue"
import type { Submission } from "src/graphql/generated/graphql"

interface Props {
  submission: Submission
}

const props = defineProps<Props>()

const { isReviewer, isReviewCoordinator, isSubmitter } = useCurrentUser()

const canViewRecordOfReview = computed(() => {
  if (!props.submission) {
    return false
  }
  return (
    isReviewer(props.submission) ||
    isReviewCoordinator(props.submission) ||
    isSubmitter(props.submission)
  )
})
</script>
