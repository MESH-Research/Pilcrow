<template>
  <q-page class="flex-center flex">
    <q-card square style="width:400px">
      <q-card-section class="bg-deep-purple-7">
        <h4 class="text-h5 text-white q-my-md">Login</h4>
      </q-card-section>
      <q-card-section>
        <q-form
          v-on:submit.prevent="onSubmit()"
          class="q-px-sm q-pt-xl q-pb-lg"
        >
          <q-input square v-model="form.username" label="Username or Email">
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>

          <q-input
            square
            v-model="form.password"
            :type="isPwd ? 'password' : 'text'"
            label="Password"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="isPwd = !isPwd"
              />
            </template>
          </q-input>
        </q-form>
        <q-banner class="text-white bg-red text-center" v-if="error">
          {{ error }}
        </q-banner>
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          @click.prevent="onSubmit()"
          unelevated
          size="lg"
          color="purple-4"
          class="full-width text-white"
          label="Login"
          :loading="loading"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          Don't have an account?
          <router-link to="/register">Register.</router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: "PageLogin",
  data() {
    return {
      isPwd: true,
      form: {
        username: "",
        password: ""
      },
      error: "",
      loading: false
    };
  },
  methods: {
    onSubmit() {
      this.error = "";
      this.loading = true;
      this.$store
        .dispatch("auth/login", {
          credentials: this.form
        })
        .then(data => {
          this.$router.push("/");
        })
        .catch(data => {
          this.error = this.$t("auth.failures." + data.error);
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>
