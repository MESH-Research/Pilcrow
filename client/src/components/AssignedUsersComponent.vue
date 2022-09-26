<template>
  <section class="column q-gutter-y-sm">
    <h3 class="q-my-none">
      {{ tp$("heading", users.length) }}
      <q-icon
        v-if="!users.length"
        color="negative"
        name="error_outline"
        :title="$t('needs_attention')"
      />
    </h3>
    <p v-if="te(tPrefix('description'))" class="q-mb-none q-mx-none">
      {{ tp$("description") }}
    </p>

    <div v-if="!users.length" class="col">
      <q-card ref="card_no_users" flat>
        <q-item class="text--grey q-pa-none">
          {{ tp$("none") }}
        </q-item>
      </q-card>
    </div>

    <q-form
      v-if="acceptMore && container.effective_role === `review_coordinator`"
      class="col q-mb-lg"
      data-cy="invitation_form"
      @submit="handleSubmit"
    >
      <div class="optional-message q-mb-sm">
        <editor-content :editor="editor" />
      </div>
      <find-user-select v-model="user" data-cy="input_user" class="q-mb-md" />
      <q-btn
        :ripple="{ center: true }"
        color="accent"
        :label="tp$('add_button.label')"
        data-cy="button-assign"
        type="submit"
        class="full-width"
      />
    </q-form>

    <div v-if="users.length">
      <user-list
        ref="userList"
        data-cy="user-list"
        :persistent-separator="relationship === 'reviewers'"
        :users="users"
        :actions="
          mutable
            ? [
                {
                  ariaLabel: tp$('unassign_button.ariaLabel'),
                  icon: 'person_remove',
                  action: 'unassign',
                  help: tp$('unassign_button.help'),
                  cyAttr: 'button_unassign',
                },
              ]
            : []
        "
        @action-click="handleUserListClick"
      />
    </div>
  </section>
</template>

<script setup>
import FindUserSelect from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import {
  UPDATE_PUBLICATION_ADMINS,
  UPDATE_PUBLICATION_EDITORS,
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  UPDATE_SUBMISSION_SUBMITERS,
} from "src/graphql/mutations"
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import { Editor, EditorContent } from "@tiptap/vue-3"
import StarterKit from "@tiptap/starter-kit"
import Placeholder from "@tiptap/extension-placeholder"
const props = defineProps({
  container: {
    type: Object,
    required: true,
  },
  relationship: {
    type: String,
    required: true,
  },
  mutable: {
    type: Boolean,
    default: false,
  },
  maxUsers: {
    type: [Boolean, Number],
    required: false,
    default: false,
  },
  containerType: {
    type: String,
    requred: false,
    default: null,
  },
})

const user = ref(null)
const containerType = computed(() => props.container.__typename.toLowerCase())
const { t, te } = useI18n()
const tPrefix = (key) => `${containerType.value}.${props.relationship}.${key}`
const tp$ = (key, ...args) => t(tPrefix(key), ...args)

const { newStatusMessage } = useFeedbackMessages()

const opts = { variables: { id: props.container.id } }
const documents = {
  submission: {
    reviewers: UPDATE_SUBMISSION_REVIEWERS,
    review_coordinators: UPDATE_SUBMISSION_REVIEW_COORDINATORS,
    submitters: UPDATE_SUBMISSION_SUBMITERS,
  },
  publication: {
    editors: UPDATE_PUBLICATION_EDITORS,
    publication_admins: UPDATE_PUBLICATION_ADMINS,
  },
}
const users = computed(() => {
  return props.container[props.relationship]
})

const acceptMore = computed(() => {
  return (
    props.mutable &&
    (props.maxUsers === false) | (users.value.length < props.maxUsers)
  )
})

const { mutate } = useMutation(
  documents[containerType.value][props.relationship],
  opts
)

const editor = new Editor({
  content: "",
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: t("submissions.invite_user.message.placeholder"),
    }),
  ],
})

async function handleSubmit() {
  if (!acceptMore.value) {
    return
  }

  try {
    await mutate({
      connect: [user.value.id],
    })
      .then(() => {
        newStatusMessage(
          "success",
          tp$("assign.success", {
            display_name: user.value.name ?? user.value.username,
          })
        )
      })
      .then(() => {
        user.value = null
      })
  } catch (error) {
    newStatusMessage("failure", tp$("assign.error"))
  }
}

async function handleUserListClick({ user }) {
  if (!props.mutable) return
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      tp$("unassign.success", {
        display_name: user.name ? user.name : user.username,
      })
    )
  } catch (error) {
    newStatusMessage("failure", tp$("unassign.error"))
  }
}
</script>

<style>
.optional-message .ProseMirror {
  background-color: #fff;
  border: 1px solid #c2c2c2;
  border-radius: 5px;
  min-height: 5rem;
  padding: 8px;
}
.optional-message .ProseMirror p.is-editor-empty:first-child::before {
  color: #666667;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>
