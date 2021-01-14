<template>
  <q-page class="flex-center flex">
    <q-card square style="width:400px">
      <q-card-section class="bg-deep-purple-7">
        <h4 class="text-h5 text-white q-my-md">Registration</h4>
        <p class="text-white">Create an account on CCR.</p>
      </q-card-section>
      <q-card-section>
        <q-form class="q-px-sm q-pt-xl q-pb-lg">
          <q-input square clearable v-model="email" type="email" label="Email">
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
          <q-input
            square
            clearable
            v-model="username"
            type="name"
            label="Full Name"
          >
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>
          <q-input
            square
            clearable
            v-model="password"
            type="password"
            label="Password"
            :type="isPwd ? 'password' : 'text'"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="isPwd ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                aria-hidden="false"
                role="button"
                tabindex="0"
                :aria-pressed="!!isPwd"
                @click="isPwd = !isPwd"
                @keydown.enter.space="isPwd = !isPwd"
                aria-label="Show Password"
              />
            </template>
          </q-input>
        </q-form>
      </q-card-section>
      <q-card-actions class="q-px-lg">
        <q-btn
          unelevated
          size="lg"
          color="deep-purple-7"
          class="full-width text-white"
          label="Get Started"
        />
      </q-card-actions>
      <q-card-section class="text-center q-pa-sm">
        <p class="text-grey-6">
          <router-link to="/login">Return to login </router-link>
        </p>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  name: "PageRegister",
  data: () => {
    return {
      isPwd: true,
      email: "",
      password: "",
      name: "",
      username: ""
    };
  }
};
</script>
