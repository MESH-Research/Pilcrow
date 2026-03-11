<template>
  <article class="submission-content" data-cy="export-content">
    <editor-content :editor="editor" />
  </article>
</template>

<script setup>
import { Editor, EditorContent } from "@tiptap/vue-3"
import SubmissionContentKit from "src/tiptap/extension-submission-content-kit"
import { computed, onBeforeUnmount } from "vue"

const props = defineProps({
  content: {
    type: Object,
    required: true
  },
  inlineComments: {
    type: Array,
    default: () => []
  },
  highlightVisibility: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(["editorReady"])

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
  extensions: [SubmissionContentKit.configure({ annotation: { annotations } })]
})

editor.on("create", () => {
  emit("editorReady")
})

onBeforeUnmount(() => {
  editor.destroy()
})
</script>
