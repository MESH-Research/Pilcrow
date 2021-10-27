<template>
  <div>
    <q-form data-cy="vueAccount" @submit="updateUser()">
      <form-section>
        <template #header>Account Information</template>
        <q-input
          v-model="form.name"
          outlined
          data-cy="update_user_name"
          label="Display Name"
        />
        <q-input
          v-model="form.email"
          outlined
          data-cy="update_user_email"
          label="Email"
        />
        <q-input
          v-model="form.username"
          outlined
          data-cy="update_user_username"
          label="Username"
        />
      </form-section>
      <form-section>
        <template #header>Update Password</template>
        <q-input
          v-model="form.password"
          outlined
          data-cy="update_user_password"
          :type="isPwd ? 'password' : 'text'"
          label="Password"
          hint="Updating this will overwrite the existing password"
        >
          <template #append>
            <q-icon
              :name="isPwd ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              @click="isPwd = !isPwd"
            />
          </template>
        </q-input>
        <q-banner
          v-if="formErrorMsg"
          dense
          class="form-error text-white bg-red text-center"
          v-text="$t(`account.update.${formErrorMsg}`)"
        />
      </form-section>
      <form-actions>
        <q-btn
          :disabled="!dirty"
          class="bg-primary text-white"
          data-cy="update_user_button_save"
          type="submit"
        >
          Save
        </q-btn>
        <q-btn
          :disabled="!dirty"
          class="bg-grey-4 ml-sm"
          data-cy="update_user_button_discard"
          @click="onRevert"
        >
          Discard Changes
        </q-btn>
      </form-actions>
    </q-form>
  </div>
</template>

<script>
import { isEqual, pick } from "lodash"
import dirtyGuard from "src/components/mixins/dirtyGuard"
import { CURRENT_USER } from "src/graphql/queries"
import { UPDATE_USER } from "src/graphql/mutations"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"

const importValidationErrors = function (error, vm) {
  const gqlErrors = error?.graphQLErrors ?? []
  var hasVErrors = false
  gqlErrors.forEach((item) => {
    const vErrors = item?.extensions?.validation ?? false
    if (vErrors !== false) {
      for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
        vm.serverValidationErrors[fieldName] = fieldErrors
      }
      hasVErrors = true
    }
  })
  return hasVErrors
}

export default {
  name: "ProfileIndex",
  components: { FormSection, FormActions },
  mixins: [dirtyGuard],
  data() {
    return {
      form: {
        id: null,
        name: "",
        email: "",
        username: "",
        password: "",
      },
      isPwd: true,
      formErrorMsg: "",
      serverValidationErrors: { "user.username": false, "user.email": false },
    }
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER,
    },
  },
  computed: {
    dirty() {
      return !isEqual(this.form, this.currentUser)
    },
  },
  mounted() {
    this.form = pick(this.currentUser, Object.keys(this.form))
  },
  methods: {
    onRevert() {
      this.dirtyDialog().onOk(() => {
        this.form = this.getStateCopy()
      })
    },
    getStateCopy() {
      return pick(this.currentUser, Object.keys(this.form))
    },
    async updateUser() {
      this.formErrorMsg = ""
      try {
        await this.$apollo.mutate({
          mutation: UPDATE_USER,
          variables: this.form,
        })
        this.$q.notify({
          color: "positive",
          message: this.$t("account.update.success"),
          icon: "check_circle",
          attrs: {
            "data-cy": "update_user_notify",
          },
          html: true,
        })
      } catch (error) {
        if (importValidationErrors(error, this)) {
          this.formErrorMsg = "update_form_validation"
        } else {
          this.formErrorMsg = "update_form_internal"
        }
      }
    },
  },
}
</script>
