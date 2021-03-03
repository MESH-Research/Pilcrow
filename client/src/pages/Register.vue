<template>
  <q-page class="flex-center flex">
    <q-card style="width: 400px">
      <q-form
        autofocus
        @submit="submit"
      >
        <q-card-section class="bg-deep-purple-7">
          <h1 class="text-h5 text-white q-my-xs">
            {{ $t("auth.register") }}
          </h1>
        </q-card-section>

        <q-card-section>
          <p>
            It only takes a minute to create an account and join our community of
            scholars.
          </p>
          <fieldset
            class="q-px-sm q-pb-lg q-gutter-y-lg column"
          >
            <q-input
              v-model.trim="form.name"
              outlined
              :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
              autocomplete="name"
              data-cy="nameField"
              bottom-slots
            />
            <q-input
              outlined
              :value="$v.form.email.$model"
              type="email"
              :label="$t('auth.fields.email')"
              autocomplete="username"
              :error="$v.form.email.$error"
              :loading="emailLoading > 0"
              debounce="500"
              bottom-slots
              data-cy="email_field"
              @change="
                e => {
                  $v.form.email.$model = e.target.value.trim();
                }
              "
            >
              <template #error>
                <!-- eslint-disable vue/no-v-html -->
                <div
                  v-if="!$v.form.email.required"
                  v-html="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.email')])" 
                />
                <!-- eslint-enable vue/no-v-html -->
                <div
                  v-if="!$v.form.email.email || !$v.form.email.serverValid"
                  v-text="$t('auth.validation.EMAIL_INVALID')"
                />
                <i18n
                  v-if="!$v.form.email.available"
                  path="auth.validation.EMAIL_IN_USE"
                  tag="div"
                  style="line-height: 1.3"
                >
                  <template #loginAction>
                    <router-link to="/login">
                      {{
                        $t("auth.login_help")
                      }}
                    </router-link>
                  </template>
                  <template #passwordAction>
                    <router-link to="/reset-password">
                      {{
                        $t("auth.password_help")
                      }}
                    </router-link>
                  </template>
                  <template #break>
                    <br>
                  </template>
                </i18n>
              </template>
            </q-input>
            <q-input
              v-model.trim="$v.form.username.$model"
              outlined
              :label="$t('auth.fields.username')"
              :error="$v.form.username.$error"
              :loading="usernameLoading > 0"
              autocomplete="nickname"
              debounce="500"
              data-cy="username_field"
              bottom-slots
            >
              <template #error>
                <div
                  v-if="!$v.form.username.required"
                  v-text="
                    $t('helpers.REQUIRED_FIELD', [$t('auth.fields.username')])
                  "
                />
                <div
                  v-if="!$v.form.username.available"
                  v-text="$t('auth.validation.USERNAME_IN_USE')"
                />
              </template>
              <template
                v-if="
                  !$v.form.username.$error &&
                    !usernameLoading &&
                    form.username.length
                "
                #append
              >
                <q-icon
                  name="done"
                  color="green-6"
                />
              </template>
              <template
                v-if="
                  !$v.form.username.$error &&
                    !usernameLoading &&
                    form.username.length
                "
                #hint
              >
                {{ $t("auth.validation.USERNAME_AVAILABLE") }}
              </template>
            </q-input>
            <new-password-input
              v-model="$v.form.password.$model"
              outlined
              :label="$t('auth.fields.password')"
              :error="$v.form.password.$error"
              :complexity="complexity"
              data-cy="password_field"
            />
          </fieldset>
          <q-banner
            v-if="formErrorMsg"
            dense
            class="form-error text-white bg-red text-center"
            v-text="$t(`auth.failures.${formErrorMsg}`)"
          />
        </q-card-section>
        <q-card-actions class="q-px-lg">
          <q-btn
            unelevated
            size="lg"
            color="deep-purple-7"
            class="full-width text-white"
            :label="$t('auth.register_action')"
            type="submit"
          />
        </q-card-actions>
        <q-card-section class="text-center q-pa-sm">
          <p>
            <router-link
              to="/login"
            >
              {{ $t("auth.register_login") }}
            </router-link>
          </p>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
import NewPasswordInput from "../components/forms/NewPasswordInput.vue";
import { validationMixin } from "vuelidate";
import { required, email } from "vuelidate/lib/validators";
import { CREATE_USER } from "src/graphql/mutations";
import appAuth from "src/components/mixins/appAuth";
import zxcvbn from "zxcvbn";
import gql from "graphql-tag";

const processValidationResult = function({ data, error }, key) {
  if (typeof error == "undefined") {
    this.serverValidationErrors[key] = false;
  } else {
    importValidationErrors(error, this);
  }
};

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
  name: "PageRegister",
  components: { NewPasswordInput },
  mixins: [validationMixin, appAuth],
  apollo: {
    "user.username": {
      query: gql`
        query usernameAvailable($username: String) {
          validateNewUser(user: { username: $username })
        }
      `,
      //We have to tell vue-apollo that we're dealing with errors to stop it from outputting to the console.  Lame.
      error: () => true,
      manual: true,
      result: processValidationResult,
      fetchPolicy: "cache-and-network",
      variables() {
        return {
          username: this.form.username
        };
      },
      skip() {
        if (!this.$v.form.username.required || this.username === "") {
          return true;
        }
        return false;
      },
      loadingKey: "usernameLoading"
    },
    "user.email": {
      query: gql`
        query emailAvailable($email: String) {
          validateNewUser(user: { email: $email })
        }
      `,
      //We have to tell vue-apollo that we're dealing with errors to stop it from outputting to the console.  Lame.
      error: () => true,
      manual: true,
      result: processValidationResult,
      fetchPolicy: "cache-and-network",
      variables() {
        return {
          email: this.form.email
        };
      },
      loadingKey: "emailLoading",
      skip() {
        if (!this.$v.form.email.required || !this.$v.form.email.email) {
          return true;
        }
        return false;
      }
    }
  },
  data: () => {
    return {
      form: {
        email: "",
        password: "",
        name: "",
        username: ""
      },
      serverValidationErrors: { "user.username": false, "user.email": false },
      usernameLoading: 0,
      emailLoading: 0,
      formLoading: 0,
      formErrorMsg: ""
    };
  },
  computed: {
    complexity() {
      return zxcvbn(this.form.password);
    },
    isServerError() {
      return (field, errorToken) => {
        if (!errorToken) {
          return (this.serverValidationErrors[field] ?? false) !== false;
        }
        return (
          this.serverValidationErrors[field]?.includes?.(errorToken) ?? false
        );
      };
    }
  },
  validations: {
    form: {
      email: {
        required,
        email,
        available(value) {
          if (value === "") {
            return true;
          }
          return !this.isServerError("user.email", "EMAIL_IN_USE");
        },
        serverValid(value) {
          if (value === "") {
            return true;
          }
          return !this.isServerError("user.email", "EMAIL_NOT_VALID");
        }
      },
      username: {
        required,
        available(value) {
          if (value === "") {
            return true;
          }
          return !this.isServerError("user.username", "USERNAME_IN_USE");
        }
      },
      password: {
        required,
        complexity() {
          return (
            this.complexity.score >= 3 &&
            !this.isServerError("user.password", "PASSWORD_NOT_COMPLEX")
          );
        }
      }
    }
  },
  methods: {
    resetServerValidation() {
      Object.entries(this.serverValidationErrors).forEach(
        ([_, value]) => (value = false)
      );
    },
    async submit() {
      this.$v.$touch();
      this.formErrorMsg = "";
      if (this.$v.$invalid) {
        this.formErrorMsg = "CREATE_FORM_VALIDATION";
        return;
      }
      this.resetServerValidation();
      try {
        await this.$apollo.mutate({
          mutation: CREATE_USER,
          variables: this.form
        });
        await this.$login(this.form);
        this.$router.push("/dashboard");
      } catch (error) {
        if (importValidationErrors(error, this)) {
          this.formErrorMsg = "CREATE_FORM_VALIDATION";
        } else {
          this.formErrorMsg = "CREATE_FORM_INTERNAL";
        }
        this.$v.$touch();
      }
    }
  }
};
</script>
