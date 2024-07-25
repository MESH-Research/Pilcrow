<template>
  <q-btn
    :aria-label="$t(`submissions.comment.reference.go_to_highlight`)"
    dense
    flat
    class="q-mr-xs"
    no-caps
    @click="setActive"
  >
    <q-icon
      v-if="comment.read_at === null"
      size="xs"
      :name="unread_name[props.comment.__typename]"
      :color="activeComment?.id == comment.id ? `primary` : `unread-comment`"
    ></q-icon>
    <q-icon
      v-else
      size="xs"
      :name="read_name[props.comment.__typename]"
      color="primary"
    ></q-icon>
    <q-tooltip
      v-if="
        props.comment.read_at === null ||
        props.comment.__typename === 'InlineComment'
      "
      >{{ toolTipContent() }}</q-tooltip
    >
  </q-btn>
</template>

<script setup>
import { inject, nextTick } from "vue"
import { useI18n } from "vue-i18n"
const { t } = useI18n()

const props = defineProps({
  comment: {
    type: Object,
    required: true,
  },
})

const unread_name = {
  OverallComment: "mark_unread_chat_alt",
  OverallCommentReply: "mark_unread_chat_alt",
  InlineComment: "mark_chat_unread",
  InlineCommentReply: "mark_chat_unread",
}

const read_name = {
  OverallComment: "chat",
  OverallCommentReply: "chat",
  InlineComment: "chat_bubble",
  InlineCommentReply: "chat_bubble",
}

const activeComment = inject("activeComment")

function toolTipContent() {
  let content = ""
  if (
    props.comment.read_at === null &&
    props.comment.__typename === "InlineComment"
  ) {
    content = t(`submissions.comment.reference.mark_read_and_go_to_highlight`)
  } else if (props.comment.read_at === null) {
    content = t(`submissions.comment.reference.mark_read`)
  } else if (props.comment.__typename === "InlineComment") {
    content = t(`submissions.comment.reference.go_to_highlight`)
  }
  return content
}

function setActive() {
  //Null the active comment first to trigger the scroll watcher
  //TODO: Do this in a more elegant way.
  activeComment.value = null
  nextTick(() => {
    activeComment.value = props.comment
  })
}
</script>
