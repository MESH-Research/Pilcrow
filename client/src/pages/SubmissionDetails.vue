<template>
  <q-page>
    <h2 class="q-pl-lg">{{ submission.title }}</h2>
    <div id="q-app">
      <div class="row q-col-gutter-lg q-pa-lg">
        <section
          class="col-md-5 col-sm-6 col-xs-12"
        >
          <h3>Add a Reviewer</h3>
          <div class="q-gutter-md row">
            <q-select
              v-model="model"
              filled
              use-input
              hide-selected
              fill-input
              input-debounce="0"
              hint="Minimum 2 characters to trigger filtering"
              style="width: 250px; padding-bottom: 32px"
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
            label="ADD"
            no-caps
          />
        </section>
        <section
          class="col-md-5 col-sm-6 col-xs-12"
        >
          <h3>All Reviewers</h3>
          <q-list
            bordered
            separator
          >
            <q-item
              v-for="user in userSearch.data"
              :key="user.id"
              data-cy="userListItem"
              class="q-px-lg"
              v-ripple
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
                >
                  {{ user.email }}
                </q-item-label>
              </q-item-section>
              <q-item-section avatar>
                <q-icon
                  color="primary"
                  name="person_remove"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </section>
      </div>
    </div>
  </q-page>
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
        data: null
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
