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
        <q-form>
          <div class="q-gutter-md column q-pl-none q-pr-md">
            <q-select
              v-model="model"
              outlined
              use-input
              hide-selected
              input-debounce="0"
              hint="Search for a user to assign."
              :options="options"
              @filter="filterFn"
              @input-value="setModel"
            >
              <template #no-option>
                <q-item>
                  <q-item-section class="text-grey">
                    No results
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
            no-caps
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
          <div v-if="userSearch.data.length">
            <q-item
              v-for="user in userSearch.data"
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
            <q-item class="text-grey">
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
import { GET_SUBMISSION } from "src/graphql/queries";
import { GET_USERS } from "src/graphql/queries";
import AvatarImage from "src/components/atoms/AvatarImage.vue";

const stringOptions = [
  'Reviewer 1', 'Reviewer 2', 'Reviewer 3', 'Reviewer 4', 'Reviewer 5'
]
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
        user: null,
      },
      userSearch: {
        data: []
      },
      current_page: 1,
      model: null,
      options: stringOptions,
    }
  },
  methods: {
    filterFn (val, update, abort) {
      if (val.length < 2) {
        abort()
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.options = stringOptions.filter(v => v.toLowerCase().indexOf(needle) > -1)
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
      }
    },
    userSearch: {
      query: GET_USERS,
      variables () {
        return {
          page:this.current_page
        }
      }
    }
  },
}
</script>
