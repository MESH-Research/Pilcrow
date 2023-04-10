<template>
  <div data-cy="previewComment">
    <q-card
      square
      class="bg-grey-1 shadow-2 q-mb-md comment full-height flex content-between"
      :aria-label="
        $t('submissions.comment_preview.ariaLabel', {
          display_label: comment.created_by.display_label,
        })
      "
    >
      <div>
        <q-card-section>
          {{ comment.submission_title }}
        </q-card-section>
        <comment-preview-header
          :comment="comment"
          bg-color="#C9E5F8"
          class="comment-header full-width"
        />
        <q-card-section>
          <!-- eslint-disable vue/no-v-html -->
          <div class="comment-preview" v-html="comment.content" />
          <!-- eslint-enable vue/no-v-html -->
        </q-card-section>
        <q-card-section
          v-if="comment.style_criteria?.length"
          class="q-mx-sm q-mb-sm q-pa-none"
        >
          <q-chip
            v-for="criteria in comment.style_criteria"
            :key="criteria.id"
            size="16px"
            :icon="criteria.icon"
            data-cy="styleCriteria"
          >
            {{ criteria.name }}
          </q-chip>
        </q-card-section>
      </div>
      <q-card-actions align="right" class="q-pa-md full-width self-end">
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
import CommentPreviewHeader from "./CommentPreviewHeader.vue"

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

<style scoped>
.comment-preview {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 4;
  -webkit-box-orient: vertical;
}
</style>
