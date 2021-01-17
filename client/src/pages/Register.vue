<template>
  <q-page class="flex-center flex">
    <q-card style="width: 400px">
      <q-card-section class="bg-deep-purple-7">
        <h4 class="text-h5 text-white q-my-xs">{{ $t("auth.register") }}</h4>
      </q-card-section>
      <q-card-section>
        <p>
          It only takes a minute to create an account and join our community of
          scholars.
        </p>
        <q-form class="q-px-sm q-pb-lg q-gutter-y-lg column">
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
            bottom-slots
          >
            <template #error>
              <div
                v-if="!$v.email.required"
                v-html="$t('helpers.REQUIRED_FIELD', [$t('auth.fields.email')])"
              />
              <div
                v-if="!$v.email.email"
                v-text="$t('auth.validation.EMAIL')"
              />
            </template>
          </q-input>
          <q-input
            outlined
            v-model.trim="$v.username.$model"
            type="input"
            :label="$t('auth.fields.username')"
            :error="$v.username.$error"
            bottom-slots
          >
            <template #error>
              <div
                v-if="!$v.username.required"
                v-text="
                  $t('helpers.REQUIRED_FIELD', [$t('auth.fields.username')])
                "
              />
            </template>
          </q-input>
          <password-field
            outlined
            :label="$t('auth.fields.password')"
            v-model="$v.password.$model"
            :error="$v.password.$error"
            :complexity="complexity"
          />
        </q-form>
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          unelevated
          size="lg"
          color="deep-purple-7"
          class="full-width text-white"
          :label="$t('auth.register_action')"
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

export default {
  name: "PageRegister",
  mixins: [validationMixin],
  components: { PasswordField },
  data: () => {
    return {
      isPwd: true,
      email: "",
      password: "",
      name: "",
      username: ""
    };
  },
  computed: {
    complexity() {
      return zxcvbn(this.password);
    }
  },
  validations: {
    email: {
      required,
      email,
    },
    username: {
      required,
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
    }
  }
};
</script>
