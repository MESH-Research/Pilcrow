<template>
  <q-btn
    :aria-label="$t(`submissions.comment.reference.go_to_highlight`)"
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
import { inject, nextTick } from "vue"

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const activeComment = inject("activeComment")

function setActive() {
  //Null the active comment first to trigger the scroll watcher
  //TODO: Do this in a more elegant way.
  activeComment.value = null
  nextTick(() => {
    activeComment.value = props.comment
  })
}
</script>
