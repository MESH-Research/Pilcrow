<template>
  <div v-if="editor" class="q-mx-md q-pa-md tiptap-editor">
    <q-btn-group spread unelevated class="block text-center q-pb-md">
      <comment-editor-button
        v-for="(button, index) in commentEditorButtons"
        :key="index"
        v-bind="button"
      />
    </q-btn-group>
    <div class="editor">
      <editor-content :editor="editor" />
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
import { ref, computed } from "vue"
import { useEditor, EditorContent } from "@tiptap/vue-3"
import { useQuasar } from "quasar"
import Bold from "@tiptap/extension-bold"
import BulletList from "@tiptap/extension-bullet-list"
import BypassStyleCriteriaDialogVue from "../dialogs/BypassStyleCriteriaDialog.vue"
import CommentEditorButton from "../atoms/CommentEditorButton.vue"
import Italic from "@tiptap/extension-italic"
import Link from "@tiptap/extension-link"
import OrderedList from "@tiptap/extension-ordered-list"
import Placeholder from "@tiptap/extension-placeholder"
import StarterKit from "@tiptap/starter-kit"

const { dialog } = useQuasar()
function dirtyDialog() {
  return dialog({
    component: BypassStyleCriteriaDialogVue,
  })
}

const editor = useEditor({
  injectCSS: true,
  extensions: [
    StarterKit.configure({
      heading: false,
    }),
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
    ariaLabel: "Toggle bold selected text",
    isActive: computed(() => editor.value.isActive("bold")),
    clickHandler: () => editor.value.chain().focus().toggleBold().run(),
    tooltipText: "Bold",
    iconName: "format_bold",
  },
  {
    ariaLabel: "Toggle italic selected text",
    isActive: computed(() => editor.value.isActive("italic")),
    clickHandler: () => editor.value.chain().focus().toggleItalic().run(),
    tooltipText: "Italic",
    iconName: "format_italic",
  },
  {
    ariaLabel: "Toggle bulleted list",
    isActive: computed(() => editor.value.isActive("bulletList")),
    clickHandler: () => editor.value.chain().focus().toggleBulletList().run(),
    tooltipText: "Bulleted list",
    iconName: "list",
  },
  {
    ariaLabel: "Toggle numbered list",
    isActive: computed(() => editor.value.isActive("orderedList")),
    clickHandler: () => editor.value.chain().focus().toggleOrderedList().run(),
    tooltipText: "Numbered list",
    iconName: "format_list_numbered",
  },
  {
    ariaLabel: "Indent list item",
    isDisabled: computed(() => !editor.value.can().sinkListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().sinkListItem("listItem").run(),
    tooltipText: "Indent list item",
    iconName: "format_indent_increase",
  },
  {
    ariaLabel: "Unindent list item",
    isDisabled: computed(() => !editor.value.can().liftListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().liftListItem("listItem").run(),
    tooltipText: "Unindent list item",
    iconName: "format_indent_decrease",
  },
  {
    ariaLabel: "Insert a link",
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => setLink(),
    tooltipText: "Insert link",
    iconName: "insert_link",
  },
  {
    ariaLabel: "Unset a link",
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => editor.value.chain().focus().unsetLink().run(),
    tooltipText: "Unset link",
    iconName: "link_off",
  },
])

function submitHandler() {
  if (hasStyleCriteria.value) {
    return true
  }
  return new Promise((resolve) => {
    dirtyDialog()
      .onOk(function () {
        resolve(true)
      })
      .onCancel(function () {
        resolve(false)
      })
  })
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
  border-radius: 5px;
  border: 1px solid rgb(56, 118, 187);
  margin-top: 10px;
}
.ProseMirror {
  background: #ddd;
  border-radius: 5px;
  min-height: 200px;
  padding: 8px;
}
.ProseMirror p.is-editor-empty:first-child::before {
  color: #18453b;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
