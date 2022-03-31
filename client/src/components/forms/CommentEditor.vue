<template>
  <div v-if="editor" class="q-mx-md q-pa-md tiptap-editor">
    <q-btn-group spread unelevated class="block text-center q-pb-md">
      <comment-editor-button
        v-for="{ button, index } in commentEditorButtons"
        :key="index"
        :aria-label="button.ariaLabel"
        :is-active="button.isActive"
        :is-disabled="button.isDisabled"
        :click-handler="button.clickHandler"
        :tooltip-text="button.tooltipText"
        :icon-name="button.iconName"
      />
      <q-btn
        aria-label="Toggle italic selected text"
        color="black"
        outline
        dense
        size="sm"
        :class="{ 'is-active': editor.isActive('italic') }"
        @click="editor.chain().focus().toggleItalic().run()"
      >
        <q-tooltip class="bg-primary">Italic</q-tooltip>
        <q-icon name="format_italic"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Toggle bulleted list"
        color="black"
        outline
        dense
        size="sm"
        :class="{ 'is-active': editor.isActive('bulletList') }"
        @click="editor.chain().focus().toggleBulletList().run()"
      >
        <q-tooltip class="bg-primary">Bulleted list</q-tooltip>
        <q-icon name="list"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Toggle numbered list"
        color="black"
        outline
        dense
        size="sm"
        :class="{ 'is-active': editor.isActive('orderedList') }"
        @click="editor.chain().focus().toggleOrderedList().run()"
      >
        <q-tooltip class="bg-primary">Numbered list</q-tooltip>
        <q-icon name="format_list_numbered"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Indent list item"
        color="black"
        outline
        dense
        size="sm"
        :disabled="!editor.can().sinkListItem('listItem')"
        @click="editor.chain().focus().sinkListItem('listItem').run()"
      >
        <q-tooltip class="bg-primary">Indent list item</q-tooltip>
        <q-icon name="format_indent_increase"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Unindent list item"
        color="black"
        outline
        dense
        size="sm"
        :disabled="!editor.can().liftListItem('listItem')"
        @click="editor.chain().focus().liftListItem('listItem').run()"
      >
        <q-tooltip class="bg-primary">Unindent list item</q-tooltip>
        <q-icon name="format_indent_decrease"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Insert a link"
        color="black"
        outline
        dense
        size="sm"
        :class="{ 'is-active': editor.isActive('link') }"
        @click="setLink"
      >
        <q-tooltip class="bg-primary">Insert link</q-tooltip>
        <q-icon name="insert_link"></q-icon>
      </q-btn>
      <q-btn
        aria-label="Unset a link"
        color="black"
        outline
        dense
        size="sm"
        :disabled="!editor.isActive('link')"
        @click="editor.chain().focus().unsetLink().run()"
      >
        <q-tooltip class="bg-primary">Unset link</q-tooltip>
        <q-icon name="link_off"></q-icon>
      </q-btn>
    </q-btn-group>
    <div class="editor">
      <editor-content
        :editor="editor"
        style="
          background: #ddd;
          min-height: 200px !important;
          border-radius: 5px;
        "
        class="q-pa-xs"
      />
    </div>
    <div class="q-pa-md q-gutter-y-sm column">
      <q-toggle
        v-for="criteria in styleCriteria"
        :key="criteria.id"
        v-model="criteria.selected"
        :label="criteria.label"
      />
    </div>
    <q-btn color="primary" @click="submitHandler()">Submit</q-btn>
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from "@tiptap/vue-3"
import StarterKit from "@tiptap/starter-kit"
import Bold from "@tiptap/extension-bold"
import Italic from "@tiptap/extension-italic"
import BulletList from "@tiptap/extension-bullet-list"
import OrderedList from "@tiptap/extension-ordered-list"
import Link from "@tiptap/extension-link"
import Placeholder from "@tiptap/extension-placeholder"
import { ref, computed } from "vue"
import CommentEditorButton from "../atoms/CommentEditorButton.vue"
// import { useDirtyGuard } from "src/use/forms"
const editor = useEditor({
  injectCSS: true,
  extensions: [
    StarterKit,
    Bold,
    Italic,
    BulletList,
    OrderedList,
    Link.configure({
      openOnClick: false,
    }),
    Placeholder.configure({
      placeholder: "Add a comment …",
    }),
  ],
})

const commentEditorButtons = ref([
  {
    ariaLabel: "Bold",
    isActive: false, //editor.value.isActive("bold"),
    clickHandler: () => () => {
      console.log(`Bold`)
    }, //editor.value.chain().focus().toggleBold().run(),
    tooltipText: "Bold",
    iconName: "format_bold",
  },
  // {
  //   ariaLabel: "",
  //   isActive: editor.isActive(""),
  //   isDisabled: false,
  //   clickHandler: () => {},
  //   tooltipText: "",
  //   iconName: "",
  // },
])

function submitHandler() {
  console.log(hasStyleCriteria.value)
  return true
}

function setLink() {
  const previousUrl = editor.value.getAttributes("link").href
  const url = window.prompt("URL", previousUrl)

  // cancelled
  if (url === null) {
    return
  }

  // empty
  if (url === "") {
    editor.value.chain().focus().extendMarkRange("link").unsetLink().run()

    return
  }

  // update link
  editor.value
    .chain()
    .focus()
    .extendMarkRange("link")
    .setLink({ href: url })
    .run()
}

const styleCriteria = ref([
  {
    id: 1,
    label: "Relevance",
    selected: false,
  },
  {
    id: 2,
    label: "Accessibility",
    selected: false,
  },
  {
    id: 3,
    label: "Coherence",
    selected: false,
  },
  {
    id: 4,
    label: "Scholarly Dialogue",
    selected: false,
  },
])

const hasStyleCriteria = computed(() => {
  return styleCriteria.value.some((criteria) => criteria.selected)
})
</script>
<style>
.tiptap-editor {
  background-color: #efefef;
  border: 1px solid rgb(56, 118, 187);
  border-radius: 5px;
  margin-top: 10px;
}
/* Placeholder (at the top) */
.ProseMirror p.is-editor-empty:first-child::before {
  content: attr(data-placeholder);
  color: #18453b;
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
