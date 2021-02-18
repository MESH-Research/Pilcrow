<template>
  <q-card-section class="q-pa-none">
    <q-card-section style="min-height: 300px;"> </q-card-section>
    <q-card-section class="bg-grey-2 justify-end row q-gutter-sm q-pa-sm">
      <q-btn :disabled="!dirty" class="bg-primary text-white">Save</q-btn>
      <q-btn :disabled="!dirty" @click="onRevert" class="bg-grey-4 "
        >Cancel</q-btn
      >
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
        first_name: "",
        last_name: "",
        email: "",
        username: ""
      }
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
