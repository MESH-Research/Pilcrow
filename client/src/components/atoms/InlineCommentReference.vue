<template>
  <q-card-section class="q-pa-sm">
    <q-btn
      dense
      flat
      icon="subdirectory_arrow_right"
      color="accent"
      class="q-px-sm q-ml-sm"
      no-caps
      :aria-label="$t('submissions.comment.reply.referenceButtonAria')"
      @click="setActive"
    >
      <div>
        {{ $t("submissions.comment.reference.go_to_highlight") }}
      </div>
    </q-btn>
  </q-card-section>
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
