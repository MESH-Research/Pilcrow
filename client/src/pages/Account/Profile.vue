<template>
  <q-card-section class="q-pa-none">
    <q-card-section style="min-height: 300px;">
      <div v-if="!editUsername" class="row q-pa-md items-center">
        <div class="col-xs-3 col-md-2 text-weight-medium">Username</div>
        <div class="col-xs-8 col-md-2">{{ form.username }}</div>
        <q-btn
          flat
          icon="edit"
          color="secondary"
          clickable
          @click="editUsername = !editUsername"
        />
      </div>
      <div v-else class="row q-pa-md q-gutter-md items-center">
        <q-input
          class="col-xs-12 col-md-4 q-pb-none"
          v-model="form.username"
          label="Username"
          debounce="250"
          :rules="[checkUsernameFormat, checkUsernameAvailable]"
        />
        <q-btn
          clickable
          color="primary"
          :disabled="!saveableUsername"
          icon="save"
        />
        <q-btn icon="cancel" @click="cancelUsernameEdit" />
      </div>
      <q-separator class="q-mt-md" />
      <ProfileDataRow
        v-if="!editPrimaryEmail"
        label="Primary Email"
        :value="form.email"
      />
      <q-form v-else class="q-gutter-sm row">
        <q-input
          class="col-md-5"
          v-model="form.email"
          label="Primary Email"
          lazy-rules
          :rules="[val => (val && val.length > 0) || 'Email can\'t be blank']"
        />
      </q-form>

      <q-form class="q-gutter-sm row">
        <q-input
          class="col-12 col-md-5"
          filled
          v-model="form.first_name"
          label="First Name"
          lazy-rules
          :rules="[val => (val && val.length > 0) || 'Please type something']"
        />
        <q-input
          class="col-12 col-md-5"
          filled
          v-model="form.last_name"
          label="Last Name"
          lazy-rules
          :rules="[val => (val && val.length > 0) || 'Please type something']"
        />

        \
      </q-form>
    </q-card-section>
    <q-card-section class="bg-grey-2 justify-end row q-gutter-sm q-pa-sm">
      <q-btn :disabled="!dirty" class="bg-primary text-white">Save</q-btn>
      <q-btn :disabled="!dirty" @click="onRevert" class="bg-grey-4 "
        >Cancel</q-btn
      >
    </q-card-section>
  </q-card-section>
</template>

<script>
import { mapGetters } from "vuex";
import { isEqual, pick } from "lodash";
import ProfileDataRow from "components/ProfileDataRow.vue";
import dirtyGuard from "components/mixins/dirtyGuard";

export default {
  name: "ProfileIndex",
  mixins: [dirtyGuard],
  components: { ProfileDataRow },
  data() {
    return {
      editUsername: false,
      editPrimaryEmail: false,
      saveableUsername: false,
      form: {
        first_name: "",
        last_name: "",
        email: "",
        username: ""
      }
    };
  },
  computed: {
    ...mapGetters("auth", ["user"]),
    dirty() {
      return !isEqual(this.form, this.getStateCopy());
    }
  },
  methods: {
    async checkUsernameAvailable(val) {
      try {
        await this.$store.dispatch("auth/validate", {
          username: this.form.username
        });
      } catch (e) {
        return "Oops! This username is not available.";
      }
      return true;
    },
    checkUsernameFormat(val) {
      let usernameRegex = /^[a-z][a-z0-9_]{2}[a-z0-9_]{0,12}$/i;
      if (!val || val.length == 0) {
        return "Usernames are required.";
      }
      if (usernameRegex.test(val)) {
        return true;
      }
      if (val.length > 15) {
        return "Usernames must be 15 characters or less.";
      }
      if (val.length < 3) {
        return "Usernames must be at least 3 characters long.";
      }
      if (!/^[a-z]/i.test(val)) {
        return "Usernames must start with a letter.";
      }
      if (!/^[a-z0-9_]{0,15}$/i.test(val)) {
        return "Usernames may only only contain letters, numbers or the _ character.";
      }
    },
    cancelUsernameEdit() {
      this.form.username = this.user.username;
      this.editUsername = false;
    },
    onRevert() {
      this.dirtyDialog().onOk(() => {
        this.form = this.getStateCopy();
      });
    },
    getStateCopy() {
      return pick(this.user, Object.keys(this.form));
    }
  },
  mounted() {
    this.form = pick(this.user, Object.keys(this.form));
  }
};
</script>

