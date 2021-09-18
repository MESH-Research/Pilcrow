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
 <q-card class="my-card">
    <q-card-section>
      <p>
        <q-avatar
          color="negative"
          text-color="white"
          icon="report_problem"
         />
         {{ $t("submissions.submitter.none") }}
      </p>
    </q-card-section>
  </q-card>
        </div>
      </section>
    </div>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Reviewer</h3>
        <q-form @submit="assignReviewer">
          <div class="q-gutter-md column q-pl-none">
            <q-select
              id="input_review_assignee"
              v-model="model"
              :options="options"
              hide-dropdown-icon
              hint="Search by username, email, or name."
              input-debounce="0"
              label="User to Assign"
              outlined
              use-input
              @filter="filterFn"
            >
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
                    <q-item-label v-if="scope.opt.name" caption>
                      {{ scope.opt.name }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
          <q-btn
            :ripple="{ center: true }"
            color="primary"
            class="text-uppercase q-mt-lg"
            label="Assign"
            data-cy="button_assign_reviewer"
            type="submit"
            no-caps
          />
        </q-form>
      </section>
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assigned Reviewers</h3>
        <div v-if="reviewers.length">
          <q-list
            ref="list_assigned_reviewers"
            bordered
            separator
            data-cy="list_assigned_reviewers"
          >
            <q-item
              v-for="(reviewer, index) in reviewers"
              :key="reviewer.pivot.id"
              data-cy="userListItem"
              class="q-px-lg"
            >
              <q-item-section top avatar>
                <avatar-image :user="reviewer" rounded />
              </q-item-section>
              <q-item-section>
                <q-item-label v-if="reviewer.name">
                  {{ reviewer.name }}
                </q-item-label>
                <q-item-label v-else>
                  {{ reviewer.username }}
                </q-item-label>
                <q-item-label caption lines="1">
                  {{ reviewer.email }}
                </q-item-label>
              </q-item-section>
              <q-item-section side center>
                <q-btn
                  :aria-label="`Unassign ${reviewer.username}`"
                  flat
                  color="primary"
                  icon="person_remove"
                  :data-cy="`button_unassign_reviewer_${index}`"
                  @click="unassignReviewer(reviewer)"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </div>
        <div v-else>
          <q-list
            ref="list_no_reviewers"
            bordered
            separator
            data-cy="list_no_reviewers"
          >
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                {{ $t("submissions.reviewer.none") }}
              </q-item-section>
            </q-item>
          </q-list>
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
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import UserList from "src/components/molecules/UserList.vue"

export default {
  components: {
    AvatarImage,
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
      model: null,
      options: [],
    }
  },
  computed: {
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
    async assignReviewer() {
      try {
        await this.$apollo
          .mutate({
            mutation: CREATE_SUBMISSION_USER,
            variables: {
              user_id: this.model.id,
              role_id: 5,
              submission_id: this.id,
            },
            refetchQueries: ["GetSubmission"],
          })
          .then(() => {
            this.makeNotify(
              "positive",
              "check_circle",
              "submissions.reviewer.assign.success",
              this.model.name ? this.model.name : this.model.username
            )
          })
          .then(() => {
            this.model = null
          })
      } catch (error) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.reviewer.assign.error"
        )
      }
    },
    async unassignReviewer(reviewer) {
      try {
        await this.$apollo.mutate({
          mutation: DELETE_SUBMISSION_USER,
          variables: {
            user_id: reviewer.pivot.user_id,
            role_id: 5,
            submission_id: this.id,
          },
          refetchQueries: ["GetSubmission"],
        })
        this.makeNotify(
          "positive",
          "check_circle",
          "submissions.reviewer.unassign.success",
          reviewer.name ? reviewer.name : reviewer.username
        )
      } catch (error) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.reviewer.unassign.error"
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
    setModel(val) {
      this.model = val
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
