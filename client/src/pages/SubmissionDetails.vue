<template>
  <div v-if="$apollo.loading" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <h2 class="q-pl-lg">Manage: {{ submission.title }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-12 col-xs-12">
        <h3>
          {{
            submitters.length > 1
              ? $t("submissions.submitter.title.plural")
              : $t("submissions.submitter.title.singular")
          }}
        </h3>
        <div v-if="submitters.length" class="q-gutter-md column q-pl-none">
          <user-list
            ref="list_assigned_submitters"
            data-cy="list_assigned_submitters"
            :users="submitters"
          />
        </div>
        <div v-else>
          <q-card ref="card_no_submitters" bordered flat>
            <q-item>
              <q-card-section avatar>
                <q-icon
                  color="negative"
                  text-color="white"
                  name="report_problem"
                  size="lg"
                />
              </q-card-section>
              <q-card-section>
                <p>
                  {{ $t("submissions.submitter.none") }}
                </p>
              </q-card-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Reviewer</h3>
        <q-form @submit="assignUser(5, `reviewer`, reviewer_candidate)">
          <div class="q-gutter-md column q-pl-none">
            <q-select
              id="input_review_assignee"
              v-model="reviewer_candidate"
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
                <q-chip data-cy="review_assignee_selected" dense square>
                  {{ scope.opt.username }} ({{ scope.opt.email }})
                </q-chip>
              </template>
              <template #option="scope">
                <q-item
                  data-cy="result_review_assignee"
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
            data-cy="button_assign_reviewer"
            label="Assign"
            type="submit"
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assigned Reviewers</h3>
        <div v-if="reviewers.length">
          <user-list
            ref="list_assigned_reviewers"
            data-cy="list_assigned_reviewers"
            :users="reviewers"
            :actions="[
              {
                ariaLabel: 'Unassign',
                icon: 'person_remove',
                action: 'unassignReviewer',
                help: 'Remove Reviewer',
                cyAttr: 'button_unassign_reviewer',
              },
            ]"
            @actionClick="handleReviewClick"
          />
        </div>
        <div v-else>
          <q-card ref="card_no_reviewers" bordered flat>
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                {{ $t("submissions.reviewer.none") }}
              </q-item-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>

    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Review Coordinator</h3>
        <q-form
          @submit="
            assignUser(4, `review_coordinator`, review_coordinator_candidate)
          "
        >
          <div class="q-gutter-md column q-pl-none">
            <q-select
              id="input_review_coordinator_assignee"
              v-model="review_coordinator_candidate"
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
                <q-chip
                  data-cy="review_coordinator_assignee_selected"
                  dense
                  square
                >
                  {{ scope.opt.username }} ({{ scope.opt.email }})
                </q-chip>
              </template>
              <template #option="scope">
                <q-item
                  data-cy="result_review_coordinator_assignee"
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
            data-cy="button_assign_review_coordinator"
            label="Assign"
            type="submit"
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Review Coordinator</h3>
        <div v-if="review_coordinators.length">
          <q-list
            ref="list_assigned_review_coordinators"
            data-cy="list_assigned_review_coordinators"
            bordered
            separator
          >
            <q-item
              v-for="(coordinator, index) in review_coordinators"
              :key="coordinator.pivot.id"
              data-cy="userListItem"
              class="q-px-lg"
            >
              <q-item-section top avatar>
                <avatar-image :user="coordinator" rounded />
              </q-item-section>
              <q-item-section>
                <q-item-label v-if="coordinator.name">
                  {{ coordinator.name }}
                </q-item-label>
                <q-item-label v-else>
                  {{ coordinator.username }}
                </q-item-label>
                <q-item-label lines="1" caption class="text--grey">
                  {{ coordinator.email }}
                </q-item-label>
              </q-item-section>
              <q-item-section side center>
                <q-btn
                  :aria-label="`Unassign ${coordinator.username}`"
                  flat
                  color="primary"
                  icon="person_remove"
                  :data-cy="`button_unassign_review_coordinator_${index}`"
                  @click="unassignUser(4, `review_coordinator`, coordinator)"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </div>
        <div v-else>
          <q-card ref="card_no_review_coordinators" bordered flat>
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                {{ $t("submissions.review_coordinator.none") }}
              </q-item-section>
            </q-item>
          </q-card>
        </div>
      </section>
    </div>
  </article>
</template>

<script>
import { GET_SUBMISSION, SEARCH_USERS } from "src/graphql/queries"
import {
  CREATE_SUBMISSION_USER,
  DELETE_SUBMISSION_USER,
} from "src/graphql/mutations"
//import AvatarImage from "src/components/atoms/AvatarImage.vue"
import UserList from "src/components/molecules/UserList.vue"

export default {
  components: {
    UserList,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      submission: {
        title: null,
        publication: null,
        users: [],
      },
      userSearch: {
        data: [],
      },
      current_page: 1,
      reviewer_candidate: null,
      review_coordinator_candidate: null,
      options: [],
    }
  },
  computed: {
    review_coordinators: function () {
      return this.filterUsersByRoleId(4)
    },
    reviewers: function () {
      return this.filterUsersByRoleId(5)
    },
    submitters: function () {
      return this.filterUsersByRoleId(6)
    },
  },
  methods: {
    filterUsersByRoleId(id) {
      return this.submission.users.filter((user) => {
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
          "data-cy": "submission_details_notify",
        },
        html: true,
      })
      this.is_submitting = false
    },
    async assignUser(role_id, role_name, candidate_model) {
      try {
        await this.$apollo
          .mutate({
            mutation: CREATE_SUBMISSION_USER,
            variables: {
              user_id: candidate_model.id,
              role_id: role_id,
              submission_id: this.id,
            },
            refetchQueries: ["GetSubmission"],
          })
          .then(() => {
            this.makeNotify(
              "positive",
              "check_circle",
              `submissions.${role_name}.assign.success`,
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
          `submissions.${role_name}.assign.error`
        )
      }
    },
    resetForm() {
      this.review_coordinator_candidate = null
      this.reviewer_candidate = null
    },
    async handleReviewClick({ user, action }) {
      switch (action) {
        case "unassignReviewer":
          await this.unassignUser("5", "reviewer", user)
      }
    },
    async unassignUser(role_id, role_name, user) {
      try {
        await this.$apollo.mutate({
          mutation: DELETE_SUBMISSION_USER,
          variables: {
            user_id: user.pivot.user_id,
            role_id: role_id,
            submission_id: this.id,
          },
          refetchQueries: ["GetSubmission"],
        })
        this.makeNotify(
          "positive",
          "check_circle",
          `submissions.${role_name}.unassign.success`,
          user.name ? user.name : user.username
        )
      } catch (error) {
        this.makeNotify(
          "negative",
          "error",
          `submissions.${role_name}.unassign.error`
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
  apollo: {
    submission: {
      query: GET_SUBMISSION,
      variables() {
        return {
          id: this.id,
        }
      },
    },
  },
}
</script>
