<template>
  <article class="submission-content" data-cy="export-content">
    <editor-content :editor="editor" />
  </article>
</template>

<script setup lang="ts">
import { Editor, EditorContent } from "@tiptap/vue-3"
import SubmissionContentKit from "src/tiptap/extension-submission-content-kit"
import { computed, onBeforeUnmount } from "vue"

import type { ExportCommentBase } from "./ExportComment.vue"

interface Props {
  content: { data: string }
  inlineComments?: ExportCommentBase[]
  highlightVisibility?: boolean
}

interface Emits {
  editorReady: []
}

const props = withDefaults(defineProps<Props>(), {
  inlineComments: () => [],
  highlightVisibility: true
})

const emit = defineEmits<Emits>()

const annotations = computed(() =>
  props.highlightVisibility
    ? props.inlineComments
        .filter((c) => c.deleted_at == null)
        .map(({ from, to, id }) => ({
          from,
          to,
          context: { id },
          active: false,
          click: () => false
        }))
    : []
)

const editor = new Editor({
  editable: false,
  content: props.content.data,
  editorProps: {
    attributes: {
      title: "Submission Title"
    }
  },
  extensions: [SubmissionContentKit.configure({ annotation: { annotations } })]
})

editor.on("create", () => {
  emit("editorReady")
})

onBeforeUnmount(() => {
  editor.destroy()
})
</script>
