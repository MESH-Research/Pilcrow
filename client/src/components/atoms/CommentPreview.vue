<template>
  <div data-cy="previewComment">
    <q-card
      square
      class="bg-grey-1 shadow-2 q-mb-md comment"
      :aria-label="
        $t('submissions.comment.ariaLabel', {
          username: comment.created_by.username,
          replies: comment.replies.length,
        })
      "
    >
      <comment-header
        :comment="comment"
        bg-color="#C9E5F8"
        class="comment-header"
      />
      <q-card-section>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <div v-html="comment.content" />
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          aria-label="View Comment"
          data-cy="viewCommentButton"
          bordered
          color="secondary"
          text-color="white"
          @click="viewComment"
        >
          <span>View Comment</span>
        </q-btn>
      </q-card-actions>

    </q-card>
  </div>
</template>

<script setup>
import CommentHeader from "./CommentHeader.vue"

defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

function viewComment() {
  console.log(`View Comment`)
}
</script>
