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

<script setup lang="ts">
import { ref } from "vue"
import type { Comment } from "src/graphql/generated/graphql"
import CommentEditor from "./forms/CommentEditor.vue"
interface Props {
  commentType?: string
  parent?: Comment | null
  replyTo?: Comment | null
  comment?: Comment | null
}

const props = withDefaults(defineProps<Props>(), {
  commentType: "InlineComment",
  parent: undefined,
  replyTo: undefined,
  comment: null
})
const scrollTarget = ref(null)
defineExpose({
  comment: props.comment,
  scrollTarget
})

interface Emits {
  cancel: []
  submit: []
}
defineEmits<Emits>()
</script>
