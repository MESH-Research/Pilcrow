<template>
  <article class="submission-content" data-cy="export-content">
    <editor-content :editor="editor" />
  </article>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment exportContent on Submission {
    content {
      data
    }
    inline_comments(createdBy: $createdBy) @skip(if: $skip_inline) {
      id
      from
      to
    }
  }
`)
</script>

<script setup lang="ts">
import { Editor, EditorContent } from "@tiptap/vue-3"
import SubmissionContentKit from "src/tiptap/extension-submission-content-kit"
import { computed, onBeforeUnmount } from "vue"

import type { exportContentFragment } from "src/graphql/generated/graphql"

interface Props {
  submission: exportContentFragment
  highlightVisibility?: boolean
}

interface Emits {
  editorReady: []
}

const props = withDefaults(defineProps<Props>(), {
  highlightVisibility: true
})

const emit = defineEmits<Emits>()

const annotations = computed(() =>
  props.highlightVisibility
    ? (props.submission.inline_comments ?? []).map(({ from, to, id }) => ({
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
  content: props.submission.content.data,
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
