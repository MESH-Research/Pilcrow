<template>
  <q-card-section class="q-pa-none">
    <q-card-section class="q-gutter-md">
      <q-input outlined v-model="form.name" label="Display Name" />
      <q-input outlined v-model="form.email" label="Email" />
      <q-input outlined v-model="form.username" label="Username" />
      <q-input outlined v-model="form.password" label="Password" filled :type="isPwd ? 'password' : 'text'" hint="Keep this strong">
        <template v-slot:append>
          <q-icon
            :name="isPwd ? 'visibility_off' : 'visibility'"
            class="cursor-pointer"
            @click="isPwd = !isPwd"
          />
        </template>
      </q-input>
    </q-card-section>
    <q-card-section class="bg-grey-2 row justify-end">
      <div class="q-gutter-md">
        <q-btn :disabled="!dirty" class="bg-primary text-white">Save</q-btn>
        <q-btn :disabled="!dirty" @click="onRevert" class="bg-grey-4 ml-sm">Cancel</q-btn>
      </div>
    </q-card-section>
  </q-card-section>
</template>

<script>
import { isEqual, pick } from "lodash";
import dirtyGuard from "components/mixins/dirtyGuard";
import { CURRENT_USER } from "src/graphql/queries";

export default {
  name: "ProfileIndex",
  mixins: [dirtyGuard],
  data() {
    return {
      form: {
        name: "",
        email: "",
        username: "",
        password: "",
      },
      isPwd: true
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
  methods: {
    onRevert() {
      this.dirtyDialog().onOk(() => {
        this.form = this.getStateCopy();
      });
    },
    getStateCopy() {
      return pick(this.currentUser, Object.keys(this.form));
    }
  },
  mounted() {
    this.form = pick(this.currentUser, Object.keys(this.form));
  }
};
</script>
