<template>
  <q-card-section v-if="referencedComment" class="q-pa-none">
    <q-btn
      dense
      flat
      icon="subdirectory_arrow_right"
      color="accent"
      class="q-pl-sm q-ml-md"
      no-caps
      :aria-label="$t('submissions.comment.reply.referenceButtonAria')"
      @click="setActive"
    >
      <avatar-image
        :user="referencedComment.created_by"
        round
        size="15px"
        class="q-mr-sm"
      />

      <div>In reply to {{ referencedComment.created_by.username }}</div>
    </q-btn>
  </q-card-section>
</template>

<script setup>
import { computed, inject, nextTick } from "vue"
import AvatarImage from "./AvatarImage.vue"

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
  replies: {
    type: Array,
    required: true,
  },
})

const referencedComment = computed(() => {
  return props.comment.reply_to_id
    ? props.replies.find((e) => e.id === props.comment.reply_to_id)
    : null
})
const activeComment = inject("activeComment")

function setActive() {
  //Null the active comment first to trigger the scroll watcher
  //TODO: Do this in a more elegant way.
  activeComment.value = null
  nextTick(() => {
    activeComment.value = referencedComment.value
  })
}
</script>
