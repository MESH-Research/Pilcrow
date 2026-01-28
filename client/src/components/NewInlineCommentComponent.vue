<template>
  <div ref="scrollTarget" />
  <q-card
    class="q-ma-md q-pa-md bg-grey-1 inline-comment-form"
    bordered
    style="border-color: rgb(82, 11, 189)"
  >
    <comment-editor
      comment-type="InlineComment"
      v-bind="props"
      @submit="$emit('submit')"
      @cancel="$emit('cancel')"
    />
  </q-card>
</template>

<script setup>
import { ref } from "vue"
import CommentEditor from "./forms/CommentEditor.vue"
const props = defineProps({
  commentType: {
    type: String,
    required: false,
    default: "InlineComment"
  },
  parent: {
    type: Object,
    default: () => {}
  },
  replyTo: {
    type: Object,
    default: () => {}
  },
  comment: {
    type: Object,
    required: false,
    default: null
  }
})
const scrollTarget = ref(null)
defineExpose({
  comment: props.comment,
  scrollTarget
})

defineEmits(["cancel", "submit"])
</script>
