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
    <div v-if="props.isInlineComment" class="q-py-md">
      <q-list>
        <q-expansion-item
          v-for="criteria in styleCriteria"
          :key="criteria.id"
          :label="criteria.label"
          popup
          expand-icon="help_outline"
          expanded-icon="expand_less"
          expand-separator
          expand-icon-toggle
        >
          <template #header>
            <q-item-section
              avatar
              @click="criteria.selected = !criteria.selected"
            >
              <q-icon :name="criteria.icon" size="sm" color="secondary" />
            </q-item-section>
            <q-item-section @click="criteria.selected = !criteria.selected">
              <q-item-label :id="`${criteria.refAttr}_${criteria.id}`">{{
                criteria.label
              }}</q-item-label>
            </q-item-section>
            <q-item-section avatar>
              <q-toggle
                v-model="criteria.selected"
                size="lg"
                :data-ref="criteria.refAttr"
                :data-cy="criteria.refAttr"
                :aria-labelledby="`${criteria.refAttr}_${criteria.id}`"
              />
            </q-item-section>
          </template>
          <q-card>
            <q-card-section>
              {{ criteria.description }}
            </q-card-section>
          </q-card>
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
  submission: {
    type: Object,
    default: null,
  },
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

const processedStyleCriteria = () => {
  const collection = []
  props.submission.publication.style_criterias.forEach((criteria) => {
    collection.push({
      id: criteria.id,
      label: criteria.name,
      refAttr: criteria.name.toLowerCase().replace(/ /g, "_"),
      selected: false,
      icon: criteria.icon,
      description: criteria.description,
    })
  })
  return collection
}
const styleCriteria = ref(processedStyleCriteria())

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

.q-icon.q-expansion-item__toggle-icon,
.q-icon.q-expansion-item__toggle-focus {
  font-size: 1.3em;
  color: black;
}

.q-expansion-item--popup.q-expansion-item--collapsed {
  padding: 0 0;
}
</style>
