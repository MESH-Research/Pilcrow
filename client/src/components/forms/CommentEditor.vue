<template>
  <q-card v-if="editor" flat class="bg-grey-1">
    <q-btn-group spread unelevated class="block text-center q-pb-md">
      <comment-editor-button
        v-for="(button, index) in commentEditorButtons"
        :key="index"
        :aria-label="button.ariaLabel"
        v-bind="button"
      />
    </q-btn-group>
    <div class="comment-editor">
      <editor-content :editor="editor" />
    </div>
    <div v-if="props.isInlineComment" class="q-py-md q-gutter-y-sm column">
      <q-list>
        <q-expansion-item
          v-for="criteria in styleCriteria"
          :key="criteria.id"
          v-model="criteria.selected"
          :label="criteria.label"
        >
          <template #header>
            <q-item-section avatar>
              <q-icon :name="criteria.icon" size="sm" color="secondary" />
            </q-item-section>
            <q-item-section>
              <q-item-label :id="`${criteria.refAttr}_${criteria.id}`">{{
                criteria.label
              }}</q-item-label>
            </q-item-section>
            <q-item-section avatar>
              <q-toggle
                v-model="criteria.selected"
                size="lg"
                :data-ref="criteria.refAttr"
                :aria-labelledby="`${criteria.refAttr}_${criteria.id}`"
              />
            </q-item-section>
          </template>
          <!-- Sample Style Criteria Description Markup -->
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
      <q-btn flat>{{ $t("guiElements.form.cancel") }}</q-btn>
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
    ariaLabel: t("guiElements.button.bold.ariaLabel"),
    isActive: computed(() => editor.value.isActive("bold")),
    clickHandler: () => editor.value.chain().focus().toggleBold().run(),
    tooltipText: t("guiElements.button.bold.tooltipText"),
    iconName: "format_bold",
  },
  {
    ariaLabel: t("guiElements.button.italic.ariaLabel"),
    isActive: computed(() => editor.value.isActive("italic")),
    clickHandler: () => editor.value.chain().focus().toggleItalic().run(),
    tooltipText: t("guiElements.button.italic.tooltipText"),
    iconName: "format_italic",
  },
  {
    ariaLabel: t("guiElements.button.bulletedList.ariaLabel"),
    isActive: computed(() => editor.value.isActive("bulletList")),
    clickHandler: () => editor.value.chain().focus().toggleBulletList().run(),
    tooltipText: t("guiElements.button.bulletedList.tooltipText"),
    iconName: "list",
  },
  {
    ariaLabel: t("guiElements.button.numberedList.ariaLabel"),
    isActive: computed(() => editor.value.isActive("orderedList")),
    clickHandler: () => editor.value.chain().focus().toggleOrderedList().run(),
    tooltipText: t("guiElements.button.numberedList.tooltipText"),
    iconName: "format_list_numbered",
  },
  {
    ariaLabel: t("guiElements.button.indent.ariaLabel"),
    isDisabled: computed(() => !editor.value.can().sinkListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().sinkListItem("listItem").run(),
    tooltipText: t("guiElements.button.indent.tooltipText"),
    iconName: "format_indent_increase",
  },
  {
    ariaLabel: t("guiElements.button.unindent.ariaLabel"),
    isDisabled: computed(() => !editor.value.can().liftListItem("listItem")),
    clickHandler: () =>
      editor.value.chain().focus().liftListItem("listItem").run(),
    tooltipText: t("guiElements.button.unindent.tooltipText"),
    iconName: "format_indent_decrease",
  },
  {
    ariaLabel: t("guiElements.button.link.ariaLabel"),
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => setLink(),
    tooltipText: t("guiElements.button.link.tooltipText"),
    iconName: "insert_link",
  },
  {
    ariaLabel: t("guiElements.button.unlink.ariaLabel"),
    isActive: computed(() => editor.value.isActive("link")),
    clickHandler: () => editor.value.chain().focus().unsetLink().run(),
    tooltipText: t("guiElements.button.unlink.tooltipText"),
    iconName: "link_off",
  },
])

function submitHandler() {
  if (hasStyleCriteria.value || !props.isInlineComment) {
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
.comment-editor .ProseMirror {
  background: #ddd;
  border-radius: 5px;
  min-height: 200px;
  padding: 8px;
}
.comment-editor .ProseMirror p.is-editor-empty:first-child::before {
  color: #18453b;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
