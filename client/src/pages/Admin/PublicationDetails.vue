<template>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.publications')"
          to="/admin/publications"
        />
        <q-breadcrumbs-el :label="$t('publications.details')" />
      </q-breadcrumbs>
    </nav>
    <div class="q-px-lg">
      <h2 class="col-sm-12" data-cy="publication_details_heading">
        {{ publication.name }}
      </h2>
      <div v-if="publication.is_publicly_visible">
        This publication is not private and is visible to all users in CCR.
      </div>
      <div v-else>
        This publication is private and meant to be invisible to those outside
        of this publication.
      </div>
    </div>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign an Editor</h3>
        <q-form @submit="assignUser(`editor`, editor_candidate)">
          <div class="q-gutter-md column q-pl-none">
            <find-user-select
              id="input_editor_assignee"
              v-model="editor_candidate"
              cy-selected-item="editor_assignee_selected"
              cy-options-item="result_editor_assignee"
            />
          </div>
          <q-btn
            :ripple="{ center: true }"
            class="q-mt-lg"
            color="primary"
            data-cy="button_assign_editor"
            label="Assign"
            type="submit"
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Editors</h3>
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
      </section>
    </div>
  </article>
</template>

<script setup>
import UserList from "src/components/molecules/UserList.vue"
import { GET_PUBLICATION } from "src/graphql/queries"
import {
  CREATE_PUBLICATION_USER,
  DELETE_PUBLICATION_USER,
} from "src/graphql/mutations"
import RoleMapper from "src/mappers/roles"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { ref, computed } from "vue"
import FindUserSelect from "src/components/forms/FindUserSelect.vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result: pubResult } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = useResult(pubResult)

const editor_candidate = ref(null)

const editors = computed(() => {
  return filterUsersByRoleId(publication.value.users, RoleMapper["editors"])
})

function filterUsersByRoleId(users, id) {
  return users.filter((user) => {
    return parseInt(user.pivot.role_id) === id
  })
}

const { notify } = useQuasar()
const { t } = useI18n()

function makeNotify(color, icon, message, display_name = null) {
  notify({
    actions: [
      {
        label: "Close",
        color: "white",
        "data-cy": "button_dismiss_notify",
      },
    ],
    timeout: 50000,
    color: color,
    icon: icon,
    message: t(message, { display_name }),
    attrs: {
      "data-cy": "publication_details_notify",
    },
    html: true,
  })
}

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
      publication_id: props.id,
    })
    makeNotify(
      "positive",
      "check_circle",
      `publications.${role_name}.assign.success`,
      candidate_model.name ? candidate_model.name : candidate_model.username
    )
    resetForm()
  } catch (error) {
    makeNotify("negative", "error", `publications.${role_name}.assign.error`)
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
      publication_id: props.id,
    })
    makeNotify(
      "positive",
      "check_circle",
      `publications.${role_name}.unassign.success`,
      user.name ? user.name : user.username
    )
  } catch (error) {
    console.log(error)
    makeNotify("negative", "error", `publications.${role_name}.unassign.error`)
  }
}
</script>
