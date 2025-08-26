<template>
  <article class="q-px-md">
    <h2>{{ $t("publication.setup_pages.users") }}</h2>
    <p>
      {{ $t("publication.users") }}
    </p>
    <q-banner
      v-if="publication.publication_admins.length === 0"
      inline-actions
      rounded
      class="highlight"
    >
      <template #avatar>
        <q-icon name="tips_and_updates" size="sm" />
      </template>
      {{ $t("publication.setup_pages.problems.no_admins") }}
    </q-banner>
    <div class="column q-gutter-md q-mb-lg">
      <assigned-users
        ref="adminUsersRef"
        data-cy="admins_list"
        t-prefix="publication.publication_admins"
        :users="publication.publication_admins"
        mutable
        :can-invite="false"
        @invite="inviteAdmin"
        @unassign="unassignAdmin"
        @reinvite="reinviteAdmin"
        @add="addAdmin"
      />
      <q-separator />
      <assigned-users
        ref="editorUsersRef"
        data-cy="editors_list"
        t-prefix="publication.editors"
        :users="publication.editors"
        mutable
        :can-invite="false"
        @unassign="unassignEditor"
        @reinvite="reinviteEditor"
        @add="addEditor"
        @invite="inviteEditor"
      />
    </div>
  </article>
</template>

<script setup lang="ts">
import AssignedUsers from "src/components/AssignedUsers.vue"
import type { SearchUsersSelected } from "src/components/forms/FindUserSelect.vue"
import type {
  AssignedUsersFragment,
  UpdatePublicationUsersMutationVariables
} from "src/gql/graphql"
import { type PublicationSetupUsersFragment } from "src/gql/graphql"
import { useFeedbackMessages } from "src/use/guiElements"

definePage({
  name: "publication:setup:users",
  meta: {
    navigation: {
      icon: "people",
      label: "Users"
    },
    crumb: {
      label: "Users",
      icon: "people"
    }
  }
})

interface Props {
  publication: PublicationSetupUsersFragment
}

const { publication } = defineProps<Props>()

const { newStatusMessage } = useFeedbackMessages()
const { t } = useI18n()

const { mutate } = useMutation(UpdatePublicationUsersDocument)

type UsersListRef = InstanceType<typeof AssignedUsers>
const adminUsersRef = useTemplateRef<UsersListRef>("adminUsersRef")
const editorUsersRef = useTemplateRef<UsersListRef>("editorUsersRef")

async function updateUsers(
  field: keyof Omit<UpdatePublicationUsersMutationVariables, "id">,
  user: AssignedUsersFragment,
  tKey: string
) {
  const messageVars = {
    display_name: user.name ?? user.username ?? user.email
  }

  try {
    await mutate({
      id: publication.id,
      [field]: [user.id]
    })
    newStatusMessage("success", t(`${tKey}.success`, messageVars))
  } catch {
    newStatusMessage("failure", t(`${tKey}.error`, messageVars))
  }
}
async function unassignAdmin(user: AssignedUsersFragment) {
  await updateUsers(
    "adminDisconnect",
    user,
    "publication.publication_admins.unassign"
  )
}

async function unassignEditor(user: AssignedUsersFragment) {
  await updateUsers("editorDisconnect", user, "publication.editors.unassign")
}

async function addEditor(user: SearchUsersSelected) {
  await updateUsers("editorConnect", user, "publication.editors.assign")
  editorUsersRef.value?.reset()
}

async function addAdmin(user: SearchUsersSelected) {
  await updateUsers(
    "adminConnect",
    user,
    "publication.publication_admins.assign"
  )
  adminUsersRef.value?.reset()
}

async function reinviteEditor(user: AssignedUsersFragment) {
  await updateUsers("editorConnect", user, "publication.editors.reinvite")
}

async function reinviteAdmin(user: AssignedUsersFragment) {
  await updateUsers(
    "adminConnect",
    user,
    "publication.publication_admins.reinvite"
  )
}

async function inviteAdmin(user: AssignedUsersFragment) {
  //TODO: Implement inviting by email.
}
async function inviteEditor(user: AssignedUsersFragment) {
  //TODO: Implement inviting by email.
}
</script>

<script lang="ts">
graphql(`
  fragment PublicationSetupUsers on Publication {
    id
    publication_admins {
      ...AssignedUsers
    }
    editors {
      ...AssignedUsers
    }
  }
`)
graphql(`
  mutation UpdatePublicationUsers(
    $id: ID!
    $editorConnect: [ID!]
    $editorDisconnect: [ID!]
    $adminConnect: [ID!]
    $adminDisconnect: [ID!]
  ) {
    updatePublication(
      publication: {
        id: $id
        editors: { connect: $editorConnect, disconnect: $editorDisconnect }
        publication_admins: {
          connect: $adminConnect
          disconnect: $adminDisconnect
        }
      }
    ) {
      ...PublicationSetupUsers
    }
  }
`)
</script>
