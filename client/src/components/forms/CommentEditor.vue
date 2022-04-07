<template>
  <q-card v-if="editor" class="q-ma-md q-pa-md tiptap-editor">
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
        :data-ref="criteria.refAttr"
        :label="criteria.label"
      />
    </div>
    <q-btn data-ref="submit" color="primary" @click="submitHandler()"
      >Submit</q-btn
    >
  </q-card>
</template>

<script setup>
import { ref, computed } from "vue"
import { useEditor, EditorContent } from "@tiptap/vue-3"
import { useQuasar } from "quasar"
import StarterKit from "@tiptap/starter-kit"
import Link from "@tiptap/extension-link"
import Placeholder from "@tiptap/extension-placeholder"
import CommentEditorButton from "../atoms/CommentEditorButton.vue"
import BypassStyleCriteriaDialogVue from "../dialogs/BypassStyleCriteriaDialog.vue"

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
      blockquote: false,
      codeblock: false,
      hardbreak: false,
      heading: false,
      horizontalrule: false,
      strike: false,
    }),
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
    refAttr: "relevance",
    selected: false,
  },
  {
    id: 2,
    label: "Accessibility",
    refAttr: "accessibility",
    selected: false,
  },
  {
    id: 3,
    label: "Coherence",
    refAttr: "coherence",
    selected: false,
  },
  {
    id: 4,
    label: "Scholarly Dialogue",
    refAttr: "scholarly_dialogue",
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
