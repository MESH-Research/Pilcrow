<template>
  <q-header>
    <q-toolbar>
      <q-space />

      <template v-if="currentUser">
        <q-btn-dropdown stretch flat :label="currentUser.username">
          <q-list>
            <q-item clickable to="/dashboard">
              <q-item-section avatar><q-icon name="dashboard"/></q-item-section>
              <q-item-section>{{ $t("header.dashboard") }}</q-item-section>
            </q-item>
            <q-item clickable to="/account/profile">
              <q-item-section avatar
                ><q-icon name="account_circle" />
              </q-item-section>
              <q-item-section>
                {{ $t("header.account_link") }}
              </q-item-section>
            </q-item>
            <q-separator />
            <q-item clickable @click="logout">
              <q-item-section avatar>
                <q-icon name="mdi-logout" />
              </q-item-section>
              <q-item-section>
                {{ $t("auth.logout") }}
              </q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </template>
      <template v-else>
        <q-btn :label="$t('auth.register')" to="/register" stretch flat />
        <q-separator vertical dark />
        <q-btn :label="$t('auth.login')" to="/login" stretch flat />
      </template>
    </q-toolbar>
    <div class="q-px-xl q-pt-lg q-pb-sm">
      <div class="text-h4 text-weight-regular">
        <span class="text-weight-medium">Public</span> Philosophy Journal
        <strong>Quarterly</strong>
      </div>
      <div class="text-subtitle">Submission Review System</div>
    </div>
    <q-img src="statics/header-back.jpg" class="header-image absolute-top" />
  </q-header>
</template>

<script>
import gql from "graphql-tag";
import appAuth from "src/components/mixins/appAuth";
import { CURRENT_USER } from "src/graphql/queries";

export default {
  name: "AppHeader",
  mixins: [appAuth],
  data() {
    return {
      currentUser: null
    };
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  },
  methods: {
    async logout() {
      const { success } = await this.$logout();
      if (success) {
        this.$router.push("/login");
      }
    }
  }
};
</script>

<style lang="sass">
.site-title a
  text-decoration: none
  color: white
.header-image
  height: 100%
  z-index: -1
  opacity: 0.2
  filter: grayscale(2%)
</style>
