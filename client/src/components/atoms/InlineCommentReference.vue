<template>
  <q-btn
    dense
    flat
    color="primary"
    class="q-mr-xs"
    no-caps
    @click="setActive"
  >
    <q-icon size="xs" name="mode_comment"></q-icon>
    <q-tooltip>{{ $t(`submissions.comment.reference.go_to_highlight`) }}</q-tooltip>
  </q-btn>
</template>

<script setup>
import { computed, inject, nextTick } from "vue"

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const referencedComment = computed(() => {
  return props.comment.reply_to_id
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
