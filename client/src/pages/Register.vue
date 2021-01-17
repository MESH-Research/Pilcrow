<template>
  <q-page class="flex-center flex">
    <q-card style="width: 400px">
      <q-card-section class="bg-deep-purple-7">
        <h4 class="text-h5 text-white q-my-xs">{{ $t("auth.register") }}</h4>
      </q-card-section>
      <q-card-section v-if="formSuccess">
        <div class="alert alert-success">
          Woo hoo, you have an account now. Ideally you would have been logged
          in and directed to your dashboard to learn about all the amazing
          things that are CCR. But for now, this exciting, stylish box is what
          you're going to have to live with.
        </div>
      </q-card-section>
      <q-card-section v-else>
        <p>
          It only takes a minute to create an account and join our community of
          scholars.
        </p>
        <q-form
          class="q-px-sm q-pb-lg q-gutter-y-lg column"
          @submit="submit"
          autofocus
        >
          <q-input
            outlined
            v-model.trim="name"
            type="input"
            :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
            autocomplete="name"
            bottom-slots
          >
          </q-input>
          <q-input
            outlined
            v-model.trim="$v.email.$model"
            type="email"
            :label="$t('auth.fields.email')"
            autocomplete="email"
            :error="$v.email.$error"
            :loading="emailLoading > 0"
            debounce="500"
            bottom-slots
          >
            <template #error>
              <div
                v-if="!$v.email.required"
                v-html="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.email')])"
              />
              <div
                v-if="!$v.email.email || !$v.email.serverValid"
                v-text="$t('auth.validation.EMAIL_INVALID')"
              />
              <i18n
                v-if="!$v.email.available"
                path="auth.validation.EMAIL_IN_USE"
                tag="div"
                style="line-height: 1.3"
              >
                <template #loginAction>
                  <router-link to="/login">{{
                    $t("auth.login_help")
                  }}</router-link>
                </template>
                <template #passwordAction>
                  <router-link to="/reset-password">{{
                    $t("auth.password_help")
                  }}</router-link>
                </template>
                <template #break><br /></template>
              </i18n>
            </template>
          </q-input>
          <q-input
            outlined
            v-model.trim="$v.username.$model"
            type="input"
            :label="$t('auth.fields.username')"
            :error="$v.username.$error"
            :loading="usernameLoading > 0"
            debounce="500"
            bottom-slots
          >
            <template #error>
              <div
                v-if="!$v.username.required"
                v-text="
                  $t('helpers.REQUIRED_FIELD', [$t('auth.fields.username')])
                "
              />
              <div
                v-if="!$v.username.available"
                v-text="$t('auth.validation.USERNAME_IN_USE')"
              />
            </template>
            <template
              #append
              v-if="!$v.username.$error && !usernameLoading && username.length"
            >
              <q-icon name="done" color="green-6" />
            </template>
            <template
              #hint
              v-if="!$v.username.$error && !usernameLoading && username.length"
              >{{ $t("auth.validation.USERNAME_AVAILABLE") }}</template
            >
          </q-input>
          <password-field
            outlined
            :label="$t('auth.fields.password')"
            v-model="$v.password.$model"
            :error="$v.password.$error"
            :complexity="complexity"
          />
        </q-form>
        <q-banner
          v-if="formErrorMsg"
          dense
          class="text-white bg-red text-center"
          v-text="$t(`auth.failures.${formErrorMsg}`)"
        />
      </q-card-section>
      <q-card-actions v-if="!formSuccess" class="q-px-lg">
        <q-btn
          unelevated
          size="lg"
          color="deep-purple-7"
          class="full-width text-white"
          :label="$t('auth.register_action')"
          @click="submit"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          <router-link to="/login"
            >{{ $t("auth.register_login") }}
          </router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import PasswordField from "../components/users/PasswordField.vue";
import { validationMixin } from "vuelidate";
import { required, email } from "vuelidate/lib/validators";
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
  mixins: [validationMixin],
  components: { PasswordField },
  apollo: {
    "user.username": {
      query: gql`
        query usernameAvailable($username: String) {
          validateNewUser(user: { username: $username })
        }
      `,
      manual: true,
      result: processValidationResult,
      fetchPolicy: "cache-and-network",
      variables() {
        return {
          username: this.username
        };
      },
      skip() {
        if (!this.$v.username.required || this.username === "") {
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
      manual: true,
      result: processValidationResult,
      fetchPolicy: "cache-and-network",
      variables() {
        return {
          email: this.email
        };
      },
      loadingKey: "emailLoading",
      skip() {
        if (!this.$v.email.required || !this.$v.email.email) {
          return true;
        }
        return false;
      }
    }
  },
  data: () => {
    return {
      email: "",
      password: "",
      name: "",
      username: "",
      serverValidationErrors: { "user.username": false, "user.email": false },
      usernameLoading: 0,
      emailLoading: 0,
      formLoading: 0,
      formErrorMsg: "",
      formSuccess: false
    };
  },
  computed: {
    complexity() {
      return zxcvbn(this.password);
    },
    isServerError() {
      return (field, errorToken) => {
        if (this.serverValidationErrors[field] === false) {
          return false;
        }
        return this.serverValidationErrors[field].includes(errorToken);
      };
    }
  },
  validations: {
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
        return this.complexity.score >= 3;
      }
    }
  },
  methods: {
    submit() {
      const { email, name, username, password } = this;
      this.$v.$touch();
      this.formErrorMsg = "";
      if (this.$v.$invalid) {
        this.formErrorMsg = "CREATE_FORM_VALIDATION";
        return;
      }

      this.$apollo
        .mutate({
          mutation: gql`
            mutation(
              $email: String!
              $name: String
              $username: String!
              $password: String!
            ) {
              createUser(
                user: {
                  name: $name
                  email: $email
                  username: $username
                  password: $password
                }
              ) {
                username
                id
                created_at
              }
            }
          `,
          variables: {
            email,
            name,
            username,
            password
          }
        })
        .then(data => {
          this.formSuccess = true;
        })
        .catch(error => {
          if (importValidationErrors(error, this)) {
            this.formErrorMsg = "CREATE_FORM_VALIDATION";
          } else {
            this.formErrorMsg = "CREATE_FORM_INTERNAL";
          }
          this.$v.$touch();
        });
    }
  }
};
</script>
