<template>
  <q-form @submit="submitHandler">
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
      <div v-if="commentType === 'InlineComment'" class="q-py-md">
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
            data-cy="criteria-item"
          >
            <template #header>
              <q-item-section
                avatar
                data-cy="criteria-icon"
                @click="criteria.selected = !criteria.selected"
              >
                <q-icon :name="criteria.icon" size="sm" color="secondary" />
              </q-item-section>
              <q-item-section @click="criteria.selected = !criteria.selected">
                <q-item-label
                  :id="`criteria-${uuid}-${criteria.id}`"
                  data-cy="criteria-label"
                  >{{ criteria.name }}</q-item-label
                >
              </q-item-section>
              <q-item-section avatar>
                <q-toggle
                  v-model="criteria.selected"
                  size="lg"
                  data-cy="criteria-toggle"
                  :aria-labelledby="`criteria-${uuid}-${criteria.id}`"
                />
              </q-item-section>
            </template>
            <q-card data-cy="criteria-description">
              <!-- eslint-disable-next-line vue/no-v-html vue/no-v-text-v-html-on-component -->
              <q-card-section v-html="criteria.description" />
            </q-card>
          </q-expansion-item>
        </q-list>
      </div>
      <q-card-actions class="q-mt-md q-pa-none" align="between">
        <q-btn type="submit" color="primary">
          {{ $t("guiElements.form.submit") }}
        </q-btn>
        <q-btn
          v-if="commentType !== 'OverallComment'"
          ref="cancel_button"
          flat
          @click="cancelHandler()"
        >
          {{ $t("guiElements.form.cancel") }}
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-form>
</template>

<script setup>
import { ref, computed, inject } from "vue"
import { useEditor, EditorContent } from "@tiptap/vue-3"
import { useMutation } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import StarterKit from "@tiptap/starter-kit"
import Link from "@tiptap/extension-link"
import Placeholder from "@tiptap/extension-placeholder"
import CommentEditorButton from "../atoms/CommentEditorButton.vue"
import BypassStyleCriteriaDialogVue from "../dialogs/BypassStyleCriteriaDialog.vue"
import {
  CREATE_OVERALL_COMMENT,
  CREATE_OVERALL_COMMENT_REPLY,
  CREATE_INLINE_COMMENT_REPLY,
  CREATE_INLINE_COMMENT,
} from "src/graphql/mutations"
import { useI18n } from "vue-i18n"
import { uniqueId } from "lodash"

const { dialog } = useQuasar()
function dirtyDialog() {
  return dialog({
    component: BypassStyleCriteriaDialogVue,
  })
}
const uuid = uniqueId()
const emit = defineEmits(["cancel", "submit"])
const props = defineProps({
  commentType: {
    type: String,
    required: false,
    default: null,
  },
  parent: {
    type: Object,
    default: () => {},
  },
  isQuoteReplying: {
    type: Boolean,
    default: false,
  },
  replyTo: {
    type: Object,
    default: () => {},
  },
  comment: {
    type: Object,
    required: false,
    default: null,
  },
})

const defaultContent = computed(() => {
  if (!props.isQuoteReplying) {
    return ""
  }
  //TODO: Make this more robust to handle multi paragraphs, etc
  return `<blockquote>${props.replyTo.content}</blockquote><p></p>`
})
const { t } = useI18n()
const editor = useEditor({
  content: defaultContent.value,
  injectCSS: true,
  extensions: [
    StarterKit.configure({
      blockquote: true,
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
const commentType = computed(
  () => props.commentType ?? props.comment.__typename
)

const isReply = computed(() =>
  ["OverallCommentReply", "InlineCommentReply"].includes(commentType.value)
)
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
const submission = inject("submission")
const mutations = {
  InlineComment: CREATE_INLINE_COMMENT,
  InlineCommentReply: CREATE_INLINE_COMMENT_REPLY,
  OverallComment: CREATE_OVERALL_COMMENT,
  OverallCommentReply: CREATE_OVERALL_COMMENT_REPLY,
}
const { mutate: createComment } = useMutation(mutations[commentType.value])
const selectedCriteria = computed(() =>
  styleCriteria.value
    .filter((criteria) => criteria.selected)
    .map((criteria) => criteria.id)
)
const hasStyleCriteria = computed(() => selectedCriteria.value.length > 0)
async function submitHandler() {
  if (!hasStyleCriteria.value && commentType.value === "InlineComment") {
    if (
      !(await new Promise((resolve) => {
        dirtyDialog()
          .onOk(function () {
            resolve(true)
          })
          .onCancel(function () {
            resolve(false)
          })
      }))
    ) {
      return
    }
  }
  if (editor.value.isEmpty) {
    return false
  }
  try {
    const args = {
      submission_id: submission.value.id,
      content: editor.value.getHTML(),
    }
    if (commentType.value === "InlineComment") {
      args.style_criteria = selectedCriteria.value
      args.from = props.comment.from
      args.to = props.comment.to
    }
    if (isReply.value) {
      args.reply_to_id = props.replyTo.id
      args.parent_id = props.parent.id
    }
    await createComment({
      ...args,
    })
      .then(() => {
        editor.value.commands.clearContent(true)
      })
      .then(() => {
        emit("submit")
      })
  } catch (error) {
    console.log("Error", error)
  }
}

function cancelHandler() {
  emit("cancel")
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

const styleCriteria = ref(
  submission.value.publication.style_criterias.map((c) => ({
    ...c,
    selected: false,
  }))
)
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

.ProseMirror blockquote {
  border-left: 4px solid #888888;
  margin-inline-start: 1em;
  padding-left: 0.5em;
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
