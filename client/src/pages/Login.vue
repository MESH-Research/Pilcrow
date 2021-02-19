<template>
  <q-page class="flex-center flex">
    <q-card
      style="width: 400px"
      square
    >
      <q-card-section class="bg-deep-purple-7 q-pa-sm">
        <div class="text-h5 text-white">
          Login
        </div>
      </q-card-section>
      <q-card-section class="q-pa-lg">
        <q-banner
          v-if="redirectUrl"
          class="text-white bg-red text-center"
          dense
          rounded
          v-text="$t(`auth.loginRequired`)"
        />
        <q-form
          class="q-px-sm q-pt-md  q-gutter-y-lg q-pb-lg"
          @submit.prevent="login()"
        >
          <q-input
            ref="username"
            outlined
            :value="$v.form.email.$model"
            :error="$v.form.email.$error"
            :label="$t('auth.fields.email')"
            autofocus
            autocomplete="username"
            @change="
              e => {
                $v.form.email.$model = e.target.value.trim();
              }
            "
            @keypress.enter="$refs.password.focus()"
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
            outlined
            :error="$v.form.password.$error"
            :label="$t('auth.fields.password')"
            autocomplete="current-password"
            @keypress.enter="login"
          >
            <template #error>
              <div
                v-if="!$v.form.password.required"
                v-text="
                  $t('helpers.REQUIRED_FIELD', [$t('auth.fields.password')])
                "
              />
            </template>
          </password-input>
        </q-form>

        <q-banner
          v-if="error"
          class="text-white bg-red text-center"
          dense
          rounded
          v-text="$t(`auth.failures.${error}`)"
        />
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          unelevated
          size="lg"
          color="purple-4"
          class="full-width text-white"
          label="Login"
          :loading="loading"
          @click.prevent="login()"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          Don't have an account?
          <router-link to="/register">
            Register.
          </router-link>
        </p>
      </q-card-section>
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
