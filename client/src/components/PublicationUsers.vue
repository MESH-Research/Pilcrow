<template>
  <q-card flat class="q-ma-none">
    <q-card-section>
      <div class="text-h3">Editors</div>
    </q-card-section>
    <q-card-section>
      <div v-if="editors.length">
        <user-list
          ref="list_assigned_editors"
          data-cy="list_assigned_editors"
          :users="editors"
          :actions="[
            {
              ariaLabel: 'Unassign',
              icon: 'person_remove',
              action: 'unassignEditor',
              help: 'Remove Editor',
              cyAttr: 'button_unassign_editor',
            },
          ]"
          @action-click="handleUserListClick"
        />
      </div>
      <div v-else>
        <q-card ref="card_no_editors" bordered flat>
          <q-item class="text--grey">
            <q-item-section avatar>
              <q-icon name="o_do_disturb_on" />
            </q-item-section>
            <q-item-section>
              {{ $t("publications.editor.none") }}
            </q-item-section>
          </q-item>
        </q-card>
      </div>
    </q-card-section>
    <q-card-actions v-if="!addMode" align="right">
      <q-btn
        icon="person_add"
        label="Add editor"
        data-cy="addEditorButton"
        flat
        @click="addMode = true"
      />
    </q-card-actions>
    <q-card-section v-if="addMode">
      <q-form @submit="assignUser(`editor`, editor_candidate)">
        <div class="q-pl-none">
          <find-user-select
            v-model="editor_candidate"
            data-cy="input_editor_assignee"
            cy-selected-item="editor_assignee_selected"
            cy-options-item="result_editor_assignee"
          >
            <template #after>
              <q-btn
                ref="assignBtn"
                :ripple="{ center: true }"
                color="primary"
                data-cy="button_assign_editor"
                label="Assign"
                type="submit"
                stretch
                @click="assignUser('editor', editor_candidate)"
              />
            </template>
          </find-user-select>
        </div>
      </q-form>
    </q-card-section>
  </q-card>
</template>

<script setup>
import UserList from "src/components/molecules/UserList.vue"
import FindUserSelect from "src/components/forms/FindUserSelect.vue"
import {
  CREATE_PUBLICATION_USER,
  DELETE_PUBLICATION_USER,
} from "src/graphql/mutations"
import RoleMapper from "src/mappers/roles"
import { useMutation } from "@vue/apollo-composable"
import { useFeedbackMessages } from "src/use/guiElements"
import { useI18n } from "vue-i18n"
import { ref, computed, toRef } from "vue"
const props = defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
const addMode = ref(false)

const publication = toRef(props, "publication")
const editor_candidate = ref(null)

const editors = computed(() => {
  return filterUsersByRoleId(publication.value.users, RoleMapper["editors"])
})

function filterUsersByRoleId(users, id) {
  return users.filter((user) => {
    return parseInt(user.pivot.role_id) === id
  })
}

const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "publication_details_notify",
  },
})

async function handleUserListClick({ user, action }) {
  switch (action) {
    case "unassignEditor":
      await unassignUser("editor", user)
      break
  }
}

const { mutate: assignUserMutate } = useMutation(CREATE_PUBLICATION_USER, {
  refetchQueries: ["GetPublication"],
})

async function assignUser(role_name, candidate_model) {
  try {
    await assignUserMutate({
      user_id: candidate_model.id,
      role_id: RoleMapper[role_name],
      publication_id: publication.value.id,
    })
    newStatusMessage(
      "success",
      t(`publications.${role_name}.assign.success`, {
        display_name: candidate_model.name
          ? candidate_model.name
          : candidate_model.username,
      })
    )
    resetForm()
  } catch (error) {
    newStatusMessage("failure", t(`publications.${role_name}.assign.error`))
  }
}

function resetForm() {
  editor_candidate.value = null
}

const { mutate: unassignUserMutate } = useMutation(DELETE_PUBLICATION_USER, {
  refetchQueries: ["GetPublication"],
})

async function unassignUser(role_name, user) {
  try {
    await unassignUserMutate({
      user_id: user.pivot.user_id,
      role_id: RoleMapper[role_name],
      publication_id: publication.value.id,
    })
    newStatusMessage(
      "success",
      t(`publications.${role_name}.unassign.success`, {
        display_name: user.name ? user.name : user.username,
      })
    )
  } catch (error) {
    console.log(error)
    newStatusMessage("failure", t(`publications.${role_name}.unassign.error`))
  }
}
</script>

<style></style>
