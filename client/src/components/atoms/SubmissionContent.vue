<template>
  <div class="row items-center justify-end q-pa-md">
    <q-select
      v-model="selectedFont"
      outlined
      :options="fonts"
      label="Font"
      style="min-width: 150px"
    />
    <div class="q-ml-md">
      <q-btn
        aria-label="Decrease Font Size"
        round
        flat
        icon="remove_circle"
        color="white"
        text-color="grey-7"
      />
      <q-btn
        aria-label="Increase Font Size"
        round
        flat
        icon="add_circle"
        color="white"
        text-color="grey-7"
      />
      <q-btn
        size="sm"
        class="q-ml-md"
        aria-label="Toggle Dark Mode"
        round
        :icon="darkMode ? `dark_mode` : `light_mode`"
        color="white"
        text-color="grey-7"
        @click="toggleDarkMode()"
      />
    </div>
  </div>
  <article
    ref="contentRef"
    data-cy="content"
    class="col-sm-9 submission-content"
    :data-visibility="props.highlightVisibility"
  >
    <bubble-menu
      v-if="editor"
      :editor="editor"
      :tippy-options="{ duration: 100 }"
      :should-show="shouldShow"
    >
      <q-btn color="white" text-color="primary" @click="addComment">
        <q-icon name="add_comment" />
      </q-btn>
    </bubble-menu>
    <editor-content :editor="editor" />
  </article>
</template>
<script setup>
import { ref, inject, computed } from "vue"
import { Editor, EditorContent, BubbleMenu } from "@tiptap/vue-3"
import Highlight from "@tiptap/extension-highlight"

import StarterKit from "@tiptap/starter-kit"
import AnnotationExtension from "src/tiptap/annotation-extension"

const props = defineProps({
  highlightVisibility: {
    type: Boolean,
    default: true,
  },
})

const submission = inject("submission")
const activeComment = inject("activeComment")

const contentRef = ref(null)

let darkMode = ref(true)
function toggleDarkMode() {
  darkMode.value = !darkMode.value
}
const fonts = ["Sans-serif", "Serif"]
let selectedFont = ref("San-serif")

const findCommentFromId = (id) =>
  submission.value.inline_comments.find((c) => c.id === id)

const onAnnotationClick = (context, { target }) => {
  //First we need to get all the comment widget elements with the same Y index
  const { top: targetTop } = target.getBoundingClientRect()
  const widgets = [...contentRef.value.querySelectorAll(".comment-widget")]
    .filter((e) => e.getBoundingClientRect().top === targetTop)
    .map((e) => e.dataset.comment)

  //Only one comment here. We're done
  if (widgets.length === 1) {
    activeComment.value = findCommentFromId(context.id)
    return
  }
  const currentIndex = widgets.indexOf(activeComment.value?.id)
  //The active comment isn't one of these, show the first
  if (currentIndex === -1) {
    activeComment.value = findCommentFromId(widgets[0])
    return
  }
  //We're at the last in the list, start over
  if (currentIndex + 1 === widgets.length) {
    activeComment.value = findCommentFromId(widgets[0])
    return
  }
  //Next in the list
  activeComment.value = findCommentFromId(widgets[currentIndex + 1])
}

const inlineComments = computed(() => submission.value?.inline_comments ?? [])
const annotations = computed(() =>
  props.highlightVisibility
    ? []
    : inlineComments.value.map(({ from, to, id }) => ({
        from,
        to,
        context: { id },
        active: id === activeComment.value?.id,
        click: onAnnotationClick,
      }))
)

const editor = new Editor({
  editable: false,
  content: submission.value.content.data,
  extensions: [
    StarterKit,
    Highlight,
    AnnotationExtension.configure({ annotations }),
  ],
})

function shouldShow({ state }) {
  return !state.selection.empty
}

function addComment() {
  const [from, to] = [
    editor.state.selection.$anchor.pos,
    editor.state.selection.$head.pos,
  ].sort((a, b) => a - b)
  const range = { from, to }
  activeComment.value = {
    __typename: "InlineComment",
    new: true,
    from,
    to,
    parent_id: null,
    id: "new",
  }
  console.log(range)
}
</script>

<style lang="scss">
.comment-highlight {
  background: #ddd;
}
.comment-highlight.active {
  background: rgb(255, 254, 169);
}
.comment-widget {
  display: inline-block;
  cursor: pointer;
  position: absolute;
  right: -50px;
  font-size: 1.4rem;
  color: $primary;
  text-align: center;
  padding-left: 0.5px;
  line-height: 1.1em;
}
.submission-content {
  counter-reset: paragraph_counter;
  font-size: 16px;
  margin: 0 auto;
  max-width: 700px;
  padding: 10px 60px 60px;
}

.submission-content p {
  position: relative;
}

.submission-content p:before {
  color: #555;
  content: "¶ " counter(paragraph_counter);
  counter-increment: paragraph_counter;
  display: block;
  font-family: Helvetica, Arial, san-serif;
  font-size: 1em;
  margin-right: 10px;
  min-width: 50px;
  position: absolute;
  right: 100%;
  text-align: right;
  top: 0;
  white-space: nowrap;
}

mark {
  color: #000;
  background-color: #bbe2e8;
}
</style>
