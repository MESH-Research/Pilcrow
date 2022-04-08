<template>
  <q-card v-if="editor" flat class="bg-grey-1">
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
    <div v-if="props.isInlineComment" class="q-py-md q-gutter-y-sm column">
      <q-list>
        <q-expansion-item
          v-for="criteria in styleCriteria"
          :key="criteria.id"
          v-model="criteria.selected"
          style="padding: 0"
        >
          <template #header>
            <q-item-section avatar>
              <q-icon :name="criteria.icon" size="sm" color="secondary" />
            </q-item-section>
            <q-item-section>{{ criteria.label }}</q-item-section>
            <q-item-section avatar>
              <q-toggle
                v-model="criteria.selected"
                size="lg"
                :data-ref="criteria.refAttr"
              />
            </q-item-section>
          </template>
          <ul>
            <li>
              Does the composer identify claims that support their argument?
            </li>
            <li>
              How does the composer explain how the claims are related to each
              other and the larger argument?
            </li>
            <li>
              Does the composer provide compelling evidence in support of their
              claims?
            </li>
            <li>
              For more creative works, how does the composer convey their
              intended message to readers, listeners, and/or reviewers?
            </li>
          </ul>
        </q-expansion-item>
      </q-list>
    </div>
    <q-card-actions class="q-mt-md q-pa-none" align="between">
      <q-btn data-ref="submit" color="primary" @click="submitHandler()">{{
        $t("guiElements.form.submit")
      }}</q-btn>
      <q-btn flat>Cancel</q-btn>
    </q-card-actions>
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
import { useI18n } from "vue-i18n"

const { dialog } = useQuasar()
function dirtyDialog() {
  return dialog({
    component: BypassStyleCriteriaDialogVue,
  })
}
const props = defineProps({
  isInlineComment: {
    type: Boolean,
    default: false,
  },
})

const { t } = useI18n()
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
      placeholder: t("submissions.comment.placeholder"),
    }),
  ],
})

const commentEditorButtons = ref([
  {
    ariaLabel: "guiElements.button.bold.ariaLabel",
    isActive: computed(() => editor.value.isActive("bold")),
    clickHandler: () => editor.value.chain().focus().toggleBold().run(),
    tooltipText: "guiElements.button.bold.tooltipText",
    iconName: "format_bold",
  },
  {
    ariaLabel: "guiElements.button.italic.ariaLabel",
    isActive: computed(() => editor.value.isActive("italic")),
    clickHandler: () => editor.value.chain().focus().toggleItalic().run(),
    tooltipText: "guiElements.button.italic.tooltipText",
    iconName: "format_italic",
  },
  {
    ariaLabel: "guiElements.button.bulletedList.ariaLabel",
    isActive: computed(() => editor.value.isActive("bulletList")),
    clickHandler: () => editor.value.chain().focus().toggleBulletList().run(),
    tooltipText: "guiElements.button.bulletedList.tooltipText",
    iconName: "list",
  },
  {
    ariaLabel: "guiElements.button.numberedList.ariaLabel",
    isActive: computed(() => editor.value.isActive("orderedList")),
    clickHandler: () => editor.value.chain().focus().toggleOrderedList().run(),
    tooltipText: "guiElements.button.numberedList.tooltipText",
    iconName: "format_list_numbered",
  },
  {
    ariaLabel: "guiElements.button.indent.ariaLabel",
    isDisabled: computed(() => !editor.value.can().sinkListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().sinkListItem("listItem").run(),
    tooltipText: "guiElements.button.indent.tooltipText",
    iconName: "format_indent_increase",
  },
  {
    ariaLabel: "guiElements.button.unindent.ariaLabel",
    isDisabled: computed(() => !editor.value.can().liftListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().liftListItem("listItem").run(),
    tooltipText: "guiElements.button.unindent.tooltipText",
    iconName: "format_indent_decrease",
  },
  {
    ariaLabel: "guiElements.button.link.ariaLabel",
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => setLink(),
    tooltipText: "guiElements.button.link.tooltipText",
    iconName: "insert_link",
  },
  {
    ariaLabel: "guiElements.button.unlink.ariaLabel",
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => editor.value.chain().focus().unsetLink().run(),
    tooltipText: "guiElements.button.unlink.tooltipText",
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
    icon: "close_fullscreen",
  },
  {
    id: 2,
    label: "Accessibility",
    refAttr: "accessibility",
    selected: false,
    icon: "accessibility",
  },
  {
    id: 3,
    label: "Coherence",
    refAttr: "coherence",
    selected: false,
    icon: "psychology",
  },
  {
    id: 4,
    label: "Scholarly Dialogue",
    refAttr: "scholarly_dialogue",
    selected: false,
    icon: "question_answer",
  },
])

const hasStyleCriteria = computed(() => {
  return styleCriteria.value.some((criteria) => criteria.selected)
})
</script>
<style>
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
