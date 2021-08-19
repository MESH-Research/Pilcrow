<template>
  <div
    v-if="$apollo.loading"
    class="q-pa-lg"
  >
    {{ $t('loading') }}
  </div>
  <article v-else>
    <h2 class="q-pl-lg">
      Manage: {{ submission.title }}
    </h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Assign a Reviewer</h3>
        <q-form @submit="assignReviewer">
          <div class="q-gutter-md column q-pl-none q-pr-md">
            <q-select
              id="review_assignee_input"
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
                <q-chip
                  data-cy="review_assignee_selected"
                  dense
                  square
                >
                  {{ scope.opt.username }} ({{ scope.opt.email }})
                </q-chip>
              </template>
              <template #option="scope">
                <q-item
                  data-cy="review_assignee_result"
                  v-bind="scope.itemProps"
                  v-on="scope.itemEvents"
                >
                  <q-item-section>
                    <q-item-label>{{ scope.opt.username }} ({{ scope.opt.email }})</q-item-label>
                    <q-item-label
                      v-if="scope.opt.name"
                      caption
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
            color="primary"
            class="text-uppercase q-mt-lg"
            label="Assign"
            type="submit"
            no-caps
            type="submit"
          />
        </q-form>
      </section>
      <section
        class="col-md-5 col-sm-6 col-xs-12"
      >
        <h3>Assigned Reviewers</h3>
        <q-list
          bordered
          separator
          data-cy="assignedReviewersList"
        >
          <div v-if="submission.users.length">
            <q-item
              v-for="user in submission.users"
              :key="user.id"
              data-cy="userListItem"
              class="q-px-lg"
            >
              <q-item-section
                top
                avatar
              >
                <avatar-image
                  :user="user"
                  rounded
                />
              </q-item-section>

              <q-item-section>
                <q-item-label v-if="user.name">
                  {{ user.name }}
                </q-item-label>
                <q-item-label v-else>
                  {{ user.username }}
                </q-item-label>
                <q-item-label
                  caption
                  lines="1"
                >
                  {{ user.email }}
                </q-item-label>
              </q-item-section>

              <q-item-section
                side
                center
              >
                <q-btn
                  :aria-label="`Unassign ${user.username}`"
                  flat
                  color="primary"
                  icon="person_remove"
                />
              </q-item-section>
            </q-item>
          </div>
          <div v-else>
            <q-item class="text--grey">
              <q-item-section avatar>
                <q-icon name="o_do_disturb_on" />
              </q-item-section>
              <q-item-section>
                No reviewers are assigned to this submission.
              </q-item-section>
            </q-item>
          </div>
        </q-list>
      </section>
    </div>
  </article>
</template>

<script>
import { GET_SUBMISSION, SEARCH_USERS } from "src/graphql/queries";
import { CREATE_SUBMISSION_USER } from "src/graphql/mutations";
import AvatarImage from "src/components/atoms/AvatarImage.vue";

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
  data() {
    return {
      submission: {
        title: null,
        publication: null,
        users: [],
        reviewers: []
      },
      userSearch: {
        data: []
      },
      assignedUsers: {
        data: []
      },
      current_page: 1,
      model: null,
      options: [],
    }
  },
  methods: {
    async assignReviewer() {
      try {
        await this.$apollo.mutate({
          mutation: CREATE_SUBMISSION_USER,
          variables: {
            user_id: this.model.id,
            role_id: 5,
            submission_id: this.id
          },
          refetchQueries: ['GetSubmission']
        }).then((createdUser) => {
          this.model = null
        })
      } catch (error) {
        console.log(error)
      }
    },
    filterFn (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.$apollo.query({
          query: SEARCH_USERS,
          variables: {
            term: needle,
            page:this.current_page
          },
        }).then(searchdata => {
            var usersList = []
            const dropdowndata = searchdata.data.userSearch.data
            dropdowndata.forEach( function(currentValue, index, dropdowndata) {
              usersList[index] = currentValue
            })
            this.options = usersList
          }).catch(error =>{
            console.log({error})
          });
      })
    },
    setModel (val) {
      this.model = val
    }
  },
  apollo: {
    submission: {
      query: GET_SUBMISSION,
      variables () {
        return {
         id: this.id,
        }
      },
    }
  },
}
</script>
