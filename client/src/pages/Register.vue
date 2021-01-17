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
        <q-form class="q-px-sm q-pb-lg q-gutter-y-md column">
          <q-input
            outlined
            v-model="email"
            type="email"
            :label="$t('auth.fields.email')"
            autocomplete="email"
          >
          </q-input>
          <q-input
            outlined
            v-model="name"
            type="input"
            :label="$t('auth.fields.name')"
            autocomplete="name"
          >
          </q-input>
          <q-input
            outlined
            v-model="username"
            type="input"
            :label="$t('auth.fields.username')"
            autocomplete="username"
          >
          </q-input>
          <password-field
            outlined
            :label="$t('auth.fields.password')"
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
import zxcvbn from "zxcvbn";

export default {
  name: "PageRegister",

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
  methods: {
    submit() {
      const { email, name, username, password } = this;
    }
  }
};
</script>
