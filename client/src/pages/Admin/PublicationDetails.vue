<template>
  <div v-if="$apollo.loading" class="q-pa-lg">
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
      <h2 class="col-sm-12" data-cy="userDetailsHeading">
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
        <q-form @submit="assignUser(3, `editor`, editor_candidate)">
          <div class="q-gutter-md column q-pl-none">
            <q-select
              id="input_editor_assignee"
              v-model="editor_candidate"
              :options="options"
              bottom-slots
              hide-dropdown-icon
              input-debounce="0"
              label="User to Assign"
              outlined
              transition-hide="none"
              transition-show="none"
              use-input
              @filter="filterFn"
            >
              <template #hint>
                <div class="text--grey">
                  Search by username, email, or name.
                </div>
              </template>
              <template #selected-item="scope">
                <q-chip data-cy="editor_assignee_selected" dense square>
                  {{ scope.opt.username }} ({{ scope.opt.email }})
                </q-chip>
              </template>
              <template #option="scope">
                <q-item
                  data-cy="result_editor_assignee"
                  v-bind="scope.itemProps"
                  v-on="scope.itemEvents"
                >
                  <q-item-section>
                    <q-item-label
                      >{{ scope.opt.username }} ({{
                        scope.opt.email
                      }})</q-item-label
                    >
                    <q-item-label
                      v-if="scope.opt.name"
                      caption
                      class="text-grey-10"
                    >
                      {{ scope.opt.name }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
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
          <q-list
            ref="list_assigned_editors"
            data-cy="list_assigned_editors"
            bordered
            separator
          >
            <q-item
              v-for="(editor, index) in editors"
              :key="editor.id"
              data-cy="userListItem"
              class="q-px-lg"
            >
              <q-item-section top avatar>
                <avatar-image :user="editor" rounded />
              </q-item-section>
              <q-item-section>
                <q-item-label v-if="editor.name">
                  {{ editor.name }}
                </q-item-label>
                <q-item-label v-else>
                  {{ editor.username }}
                </q-item-label>
                <q-item-label lines="1" caption class="text--grey">
                  {{ editor.email }}
                </q-item-label>
              </q-item-section>
              <q-item-section side center>
                <q-btn
                  :aria-label="`Unassign ${editor.username}`"
                  flat
                  color="primary"
                  icon="person_remove"
                  :data-cy="`button_unassign_editor_${index}`"
                  @click="unassignUser(3, `editor`, editor)"
                />
              </q-item-section>
            </q-item>
          </q-list>
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

<script>
import { GET_PUBLICATION, SEARCH_USERS } from "src/graphql/queries"
import {
  CREATE_PUBLICATION_USER,
  DELETE_PUBLICATION_USER,
} from "src/graphql/mutations"
import AvatarImage from "src/components/atoms/AvatarImage.vue"

export default {
  components: {
    AvatarImage,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  apollo: {
    publication: {
      query: GET_PUBLICATION,
      variables() {
        return {
          id: this.id,
        }
      },
    },
  },
  data() {
    return {
      options: [],
      editor_candidate: null,
    }
  },
  computed: {
    editors: function () {
      return this.filterUsersByRoleId(3)
    },
  },
  methods: {
    filterUsersByRoleId(id) {
      return this.publication.users.filter((user) => {
        return parseInt(user.pivot.role_id) === id
      })
    },
    makeNotify(color, icon, message, display_name = null) {
      this.$q.notify({
        actions: [
          {
            label: "Close",
            color: "white",
            attrs: {
              "data-cy": "button_dismiss_notify",
            },
          },
        ],
        timeout: 50000,
        color: color,
        icon: icon,
        message: this.$t(message, { display_name }),
        attrs: {
          "data-cy": "publication_details_notify",
        },
        html: true,
      })
      this.is_submitting = false
    },
    async assignUser(role_id, role_name, candidate_model) {
      try {
        await this.$apollo
          .mutate({
            mutation: CREATE_PUBLICATION_USER,
            variables: {
              user_id: candidate_model.id,
              role_id: role_id,
              publication_id: this.id,
            },
            refetchQueries: ["GetPublication"],
          })
          .then(() => {
            this.makeNotify(
              "positive",
              "check_circle",
              `publications.${role_name}.assign.success`,
              candidate_model.name
                ? candidate_model.name
                : candidate_model.username
            )
          })
          .then(() => {
            this.resetForm()
            candidate_model = null
          })
      } catch (error) {
        this.makeNotify(
          "negative",
          "error",
          `publications.${role_name}.assign.error`
        )
      }
    },
    resetForm() {
      this.editor_candidate = null
    },
    async unassignUser(role_id, role_name, user) {
      try {
        await this.$apollo.mutate({
          mutation: DELETE_PUBLICATION_USER,
          variables: {
            user_id: user.id,
            role_id: role_id,
            publication_id: this.id,
          },
          refetchQueries: ["GetPublication"],
        })
        this.makeNotify(
          "positive",
          "check_circle",
          `publications.${role_name}.unassign.success`,
          user.name ? user.name : user.username
        )
      } catch (error) {
        this.makeNotify(
          "negative",
          "error",
          `publications.${role_name}.unassign.error`
        )
      }
    },
    filterFn(val, update) {
      update(() => {
        const needle = val.toLowerCase()
        this.$apollo
          .query({
            query: SEARCH_USERS,
            variables: {
              term: needle,
              page: this.current_page,
            },
          })
          .then((searchdata) => {
            var usersList = []
            const dropdowndata = searchdata.data.userSearch.data
            dropdowndata.forEach(function (currentValue, index) {
              usersList[index] = currentValue
            })
            this.options = usersList
          })
          .catch((error) => {
            console.log({ error })
          })
      })
    },
  },
}
</script>
