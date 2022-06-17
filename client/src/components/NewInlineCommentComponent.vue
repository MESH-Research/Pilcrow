<template>
  <q-card
    class="q-ma-md q-pa-md bg-grey-1"
    bordered
    style="border-color: rgb(56, 118, 187)"
  >
    <comment-editor v-bind="props" />
  </q-card>
</template>

<script setup>
import CommentEditor from "./forms/CommentEditor.vue"
const props = defineProps({
  commentType: {
    type: String,
    required: false,
    default: null,
  },
  parent: {
    type: Object,
    default: () => {},
  },
  replyTo: {
    type: Object,
    default: () => {},
  },
  comment: {
    type: Object,
    required: false,
    default: null,
  },
})
</script>
