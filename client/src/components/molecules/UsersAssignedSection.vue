<template>
  <div class="row q-col-gutter-lg q-pa-lg">
    <section class="col-md-5 col-sm-6 col-xs-12">
      <h3>Assign Users Section</h3>
      <q-form @submit="assignUser">
        <div class="q-gutter-md column q-pl-none">
          <q-select
            v-model="model"
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
              <div class="text--grey">Search by username, email, or name.</div>
            </template>
            <template #selected-item="scope">
              <q-chip data-cy="assignee_selected" dense square>
                {{ scope.opt.username }} ({{ scope.opt.email }})
              </q-chip>
            </template>
            <template #option="scope">
              <q-item
                data-cy="result_assignee"
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
          data-cy="button_assign_user"
          label="Assign"
          type="submit"
        />
      </q-form>
    </section>
    <section class="col-md-5 col-sm-6 col-xs-12">
      <h3>Users Assigned Section</h3>
      <div v-if="users.length">
        <user-list
          ref="list_assigned_users"
          data-cy="list_assigned_users"
          :users="users_assigned"
        />
      </div>
      <div v-else>
        <q-card ref="card_no_users" bordered flat>
          <q-item class="text--grey">
            <q-item-section avatar>
              <q-icon name="o_do_disturb_on" />
            </q-item-section>
            <q-item-section>No Users Assigned</q-item-section>
          </q-item>
        </q-card>
      </div>
    </section>
  </div>
</template>

<script>
import UserList from "src/components/molecules/UserList.vue"
export default {
  name: "UsersAssignedSection",
  components: { UserList },
  props: {
    users: {
      type: Array,
      required: true,
    },
    dataCy: {
      type: String,
      default: "users_assigned_section",
    },
  },
  emits: ["assign-user"],
  setup() {
    function assignUser() {
      console.log("Hello World")
    }
    function filterFn(val, update) {
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
    }
    return {
      assignUser: assignUser,
      filterFn: filterFn,
    }
  },
}
</script>
