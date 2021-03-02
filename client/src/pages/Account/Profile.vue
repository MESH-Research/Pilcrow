<template>
  <q-card-section class="q-pa-none">
    <q-card-section class="q-gutter-md">
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
      <q-input
        v-model="form.password"
        outlined
        label="Password"
        data-cy="update_user_password"
        :type="isPwd ? 'password' : 'text'"
        hint="Updating this will overwrite the existing password"
      >
        <template v-slot:append>
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
    </q-card-section>
    <q-card-section class="bg-grey-2 row justify-end">
      <div class="q-gutter-md">
        <q-btn
          :disabled="!dirty"
          class="bg-primary text-white"
          data-cy="update_user_button_save"
          @click="updateUser()"
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
      </div>
    </q-card-section>
  </q-card-section>
</template>

<script>
import { isEqual, pick } from "lodash";
import dirtyGuard from "src/components/mixins/dirtyGuard";
import { CURRENT_USER } from "src/graphql/queries";
import { UPDATE_USER } from "src/graphql/mutations";

const importValidationErrors = function(error, vm) {
  const gqlErrors = error?.graphQLErrors ?? [];
  var hasVErrors = false;
  gqlErrors.forEach(item => {
    const vErrors = item?.extensions?.validation ?? false;
    if (vErrors !== false) {
      for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
        vm.serverValidationErrors[fieldName] = fieldErrors;
      }
      hasVErrors = true;
    }
  });
  return hasVErrors;
};

export default {
  name: "ProfileIndex",
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
    };
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  },
  computed: {
    dirty() {
      return !isEqual(this.form, this.currentUser);
    }
  },
  mounted() {
    this.form = pick(this.currentUser, Object.keys(this.form));
  },
  methods: {
    onRevert() {
      this.dirtyDialog().onOk(() => {
        this.form = this.getStateCopy();
      });
    },
    getStateCopy() {
      return pick(this.currentUser, Object.keys(this.form));
    },
    async updateUser() {
      this.formErrorMsg = "";
      try {
        await this.$apollo.mutate({
          mutation: UPDATE_USER,
          variables: this.form
        });
        this.$q.notify({
          color: "positive",
          message: this.$t("account.update.success"),
          icon: "check_circle",
          attrs: {
            'data-cy': 'update_user_notify'
          },
          html: true
        });
      } catch (error) {
        if (importValidationErrors(error, this)) {
          this.formErrorMsg = "update_form_validation";
        } else {
          this.formErrorMsg = "update_form_internal";
        }
      }
    }
  }
};
</script>
