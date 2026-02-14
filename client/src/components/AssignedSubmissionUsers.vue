<template>
  <section class="column q-gutter-y-sm">
    <h3 class="q-my-none">
      {{ pt("heading", users.length) }}
      <q-icon
        v-if="!users.length"
        color="negative"
        name="error_outline"
        :title="$t('needs_attention')"
      />
    </h3>
    <p v-if="pte('description')" class="q-mb-none q-mx-none">
      {{ pt("description") }}
    </p>

    <div v-if="!users.length" class="col">
      <q-card ref="card_no_users" flat>
        <q-card-section class="text--grey q-pa-none">
          {{ pt("none") }}
        </q-card-section>
      </q-card>
    </div>

    <q-form
      v-if="acceptMore"
      class="col q-mb-lg"
      data-cy="invitation_form"
      @submit="handleSubmit"
    >
      <find-user-select v-model="user" data-cy="input_user" class="q-mb-md" />
      <div class="optional-message q-mb-md">
        <editor-content :editor="editor" />
      </div>
      <q-btn
        :ripple="{ center: true }"
        color="accent"
        :label="pt('add_button.label')"
        data-cy="button-assign"
        type="submit"
        class="full-width"
      />
    </q-form>

    <div v-if="users.length">
      <user-list
        ref="userList"
        data-cy="user-list"
        :users="users"
        :actions="
          mutable
            ? [
                {
                  ariaLabel: pt('unassign_button.ariaLabel'),
                  icon: 'person_remove',
                  action: 'unassign',
                  help: pt('unassign_button.help'),
                  cyAttr: 'button_unassign'
                }
              ]
            : []
        "
        @action-click="handleUserListClick"
        @reinvite="reinviteUser"
      />
    </div>
  </section>
</template>

<script setup lang="ts">
import ReinviteUserDialog from "./dialogs/ReinviteUserDialog.vue"
import FindUserSelect from "./forms/FindUserSelect.vue"
import type { FoundUser, FindUserSelectValue } from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import { useFeedbackMessages } from "src/use/guiElements"
import { useMutation } from "@vue/apollo-composable"
import {
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  UPDATE_SUBMISSION_SUBMITERS,
  INVITE_REVIEWER,
  INVITE_REVIEW_COORDINATOR
} from "src/graphql/mutations"
import { computed, ref } from "vue"
import type { DocumentNode } from "graphql"
import { useI18nPrefix } from "src/use/i18nPrefix"
import { useEditor, EditorContent } from "@tiptap/vue-3"
import StarterKit from "@tiptap/starter-kit"
import Placeholder from "@tiptap/extension-placeholder"
import { useQuasar } from "quasar"
import type { Submission } from "src/graphql/generated/graphql"

const { dialog } = useQuasar()

interface Props {
  container: Submission
  roleGroup: string
  mutable?: boolean
  maxUsers?: boolean | number
  containerType?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  mutable: false,
  maxUsers: false,
  containerType: null
})

const user = ref<FindUserSelectValue>(null)
const containerType = computed(() => props.container.__typename.toLowerCase())
const { pt, pte, t } = useI18nPrefix(
  () => `${containerType.value}.${props.roleGroup}`
)

const { newStatusMessage } = useFeedbackMessages()

interface RoleMutations {
  update: DocumentNode
  invite: DocumentNode
}

interface SubmissionMutationVars {
  id?: string
  connect?: string[]
  disconnect?: string[]
  email?: string
  message?: string
}

const mutations: Record<string, RoleMutations> = {
  reviewers: {
    update: UPDATE_SUBMISSION_REVIEWERS,
    invite: INVITE_REVIEWER
  },
  review_coordinators: {
    update: UPDATE_SUBMISSION_REVIEW_COORDINATORS,
    invite: INVITE_REVIEW_COORDINATOR
  },
  submitters: {
    update: UPDATE_SUBMISSION_SUBMITERS,
    invite: UPDATE_SUBMISSION_SUBMITERS // TODO: Enable submitter invitation
  }
}
const setMutationType = computed(() => {
  const type = mutations[props.roleGroup]
  if (typeof user.value === "string") {
    return type.invite
  }
  return type.update
})
const { mutate } = useMutation<unknown, SubmissionMutationVars>(
  setMutationType,
  { variables: { id: props.container.id } }
)

const users = computed(() => {
  return props.container[props.roleGroup]
})

const acceptMore = computed(() => {
  return (
    props.mutable &&
    (props.maxUsers === false || users.value.length < props.maxUsers) &&
    props.container.effective_role === `review_coordinator`
  )
})

const editor = useEditor({
  editorProps: {
    attributes: {
      title: t("submissions.invite_user.message.placeholder")
    }
  },
  content: "",
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: t("submissions.invite_user.message.placeholder")
    })
  ]
})

function resetForm() {
  user.value = null
  editor.value.commands.clearContent(true)
  editor.value.commands.blur()
}

async function handleSubmit() {
  if (!acceptMore.value) {
    return
  }
  if (user.value == null) {
    newStatusMessage("failure", pt("assign.no_value"))
    return
  }
  // TODO: Attempt to assign instead of invite when user.value matches a known user
  if (typeof user.value === "string") {
    inviteUser(user.value)
  } else {
    assignUser(user.value)
  }
}

function processErrorsForEmailValidation(
  errorsFromCatch: {
    graphQLErrors: Array<{
      extensions: { validation: Record<string, string[]> }
    }>
  },
  email: string
) {
  const v = errorsFromCatch.graphQLErrors[0].extensions.validation
  if (!Object.hasOwn(v, "input.email")) {
    return
  }
  const key = v["input.email"][0]
  if (key === "NOT_UNIQUE") {
    newStatusMessage(
      "failure",
      pt("invite.NOT_UNIQUE", { display_name: email })
    )
  }
  if (key === "The input.email must be a valid email address.") {
    newStatusMessage("failure", pt("invite.invalid_email"))
  }
}

async function inviteUser(email: string) {
  await mutate({
    email,
    message: editor.value.getText()
  })
    .then(() => {
      resetForm()
    })
    .catch((error) => {
      processErrorsForEmailValidation(error, email)
    })
}

async function assignUser(selectedUser: FoundUser) {
  try {
    await mutate({
      connect: [selectedUser.id]
    })
      .then(() => {
        newStatusMessage(
          "success",
          pt("assign.success", {
            display_name: selectedUser.name ?? selectedUser.username
          })
        )
      })
      .then(() => {
        resetForm()
      })
  } catch (error) {
    newStatusMessage("failure", pt("assign.error"))
  }
}

async function reinviteUser({ user }: { user: FoundUser }) {
  await new Promise((resolve) => {
    dirtyDialog(user)
      .onOk(function () {
        resolve(true)
      })
      .onCancel(function () {
        resolve(false)
      })
  })
  {
    return
  }
}
function dirtyDialog(user: FoundUser) {
  return dialog({
    component: ReinviteUserDialog,
    componentProps: {
      roleGroup: props.roleGroup,
      email: user.email,
      submissionId: props.container.id
    }
  })
}

async function handleUserListClick({ user }: { user: FoundUser }) {
  if (!props.mutable) return
  try {
    await mutate({ disconnect: [user.id] })
    newStatusMessage(
      "success",
      pt("unassign.success", {
        display_name: user.name ? user.name : user.username
      })
    )
  } catch (error) {
    newStatusMessage("failure", pt("unassign.error"))
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
