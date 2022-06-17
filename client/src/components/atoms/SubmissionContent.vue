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
  <article class="col-sm-9 submission-content">
    <h1>Embedded Buttons</h1>
    <sample-submission-content icon-name="question_answer" />
    <h1>Embedded Buttons</h1>
    <sample-submission-content icon-name="chat" />
    <h1>Embedded Buttons</h1>
    <sample-submission-content icon-name="try" />
    <h1>Embedded Buttons</h1>
    <sample-submission-content icon-name="history_edu" />
    <h1>Grouped Buttons</h1>
    <sample-submission-content-grouped icon-name="question_answer" />
    <h1>Grouped Buttons</h1>
    <sample-submission-content-criteria />
    <h1>Grouped Avatars</h1>
    <sample-submission-content-avatars />
  </article>
</template>
<script setup>
import { ref } from "vue"
import { Editor } from "@tiptap/vue-3"
import Highlight from "@tiptap/extension-highlight"
import StarterKit from "@tiptap/starter-kit"
import SampleSubmissionContent from "./SampleSubmissionContent.vue"
import SampleSubmissionContentGrouped from "./SampleSubmissionContentGrouped.vue"
import SampleSubmissionContentCriteria from "./SampleSubmissionContentCriteria.vue"
import SampleSubmissionContentAvatars from "./SampleSubmissionContentAvatars.vue"
  <article
    ref="contentRef"
    data-cy="content"
    class="col-sm-9 submission-content"
  >
    <editor-content :editor="editor" />
  </article>
</template>
<script setup>
import { ref, inject, computed } from "vue"
import { Editor, EditorContent } from "@tiptap/vue-3"
import Highlight from "@tiptap/extension-highlight"
import StarterKit from "@tiptap/starter-kit"
import AnnotationExtension from "src/tiptap/annotation-extension"

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
  inlineComments.value.map(({ from, to, id }) => ({
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
console.log(typeof editor)
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
  line-height: 1.8;
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

.highlight {
  color: #000;
  background-color: #bbe2e8;
}
</style>
