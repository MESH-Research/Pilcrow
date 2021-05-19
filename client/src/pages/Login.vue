<template>
  <q-page
    class="flex-center flex q-pa-md"
    data-cy="vueLogin"
  >
    <q-card
      style="width: 400px"
      square
    >
      <q-form
        @submit="login()"
      >
        <q-card-section class="bg-deep-purple-7 q-pa-sm">
          <h1 class="text-h5 text-white">
            Login
          </h1>
        </q-card-section>
        <q-card-section class="q-pa-lg ">
          <fieldset class="q-px-sm q-pt-md  q-gutter-y-lg q-pb-lg">
            <q-banner
              v-if="redirectUrl"
              class="text-white bg-red text-center"
              dense
              rounded
              v-text="$t(`auth.loginRequired`)"
            />

            <q-input
              ref="username"
              :value="$v.form.email.$model"
              :error="$v.form.email.$error"
              :label="$t('auth.fields.email')"
              autofocus
              outlined
              data-cy="email_field"
              autocomplete="username"
              @change="
                e => {
                  $v.form.email.$model = e.target.value.trim();
                }
              "
            >
              <template #error>
                <div
                  v-if="!$v.form.email.required"
                  v-text="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.email')])"
                />
                <div
                  v-if="!$v.form.email.email"
                  v-text="$t('auth.validation.EMAIL_INVALID')"
                />
              </template>
            </q-input>

            <password-input
              ref="password"
              v-model="$v.form.password.$model"
              :error="$v.form.password.$error"
              :label="$t('auth.fields.password')"
              outlined
              data-cy="password_field"
              autocomplete="current-password"
              @keypress.enter="login"
            >
              <template #hint>
                <div
                  v-if="!$v.form.password.required"
                  v-text="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.password')])"
                />
              </template>
            </password-input>
          </fieldset>

          <q-banner
            v-if="error"
            class="text-white bg-red text-center"
            dense
            rounded
            :data-error="error"
            data-cy="authFailureMessages"
            v-text="$t(`auth.failures.${error}`)"
          />
        </q-card-section>
        <q-card-actions class="q-px-lg">
          <q-btn
            unelevated
            size="lg"
            color="deep-purple-7"
            class="full-width text-white"
            label="Login"
            :loading="loading"
            type="submit"
          />
        </q-card-actions>
        <q-card-section class="text-center q-pa-sm">
          <p>
            Don't have an account?
            <router-link to="/register">
              Register.
            </router-link>
          </p>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
import PasswordInput from "src/components/forms/PasswordInput.vue";
import { validationMixin } from "vuelidate";
import { required, email } from "vuelidate/lib/validators";
import appAuth from "src/components/mixins/appAuth";

export default {
  name: "PageLogin",
  components: { PasswordInput },
  mixins: [validationMixin, appAuth],
  data() {
    return {
      form: {
        email: "",
        password: ""
      },
      error: "",
      loading: false,
      redirectUrl: null
    };
  },
  validations: {
    form: {
      email: {
        email,
        required
      },
      password: {
        required
      }
    }
  },
  mounted() {
    this.redirectUrl = this.$q.sessionStorage.getItem("loginRedirect");
    this.$q.sessionStorage.remove("loginRedirect");
  },
  methods: {
    async login() {
      this.error = "";
      this.$v.$touch();
      if (this.$v.$invalid) {
        this.error = "LOGIN_FORM_VALIDATION";
        return false;
      }
      const { success, errors } = await this.$login(this.form);

      if (success) {
        this.$router.push(this.redirectUrl ?? "/dashboard");
      } else {
        this.error = errors.pop();
      }
    }
  }
};
</script>
