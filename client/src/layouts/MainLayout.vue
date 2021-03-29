<template>
  <q-layout view="lhh lpr lFf">
    <app-header
      v-model="leftDrawerOpen"
      drawer
    />
    <q-drawer
      id="sidebar"
      v-model="leftDrawerOpen"
      show-if-above
      :width="200"
      content-class="sidebar"
    >
      <q-scroll-area class="sidebar-nav">
        <q-list>
          <q-item
            v-ripple
            to="/dashboard"
          >
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.dashboard") }}
            </q-item-section>
          </q-item>
          <q-item
            v-ripple
            to="/account/profile"
          >
            <q-item-section avatar>
              <q-icon name="account_circle" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.account_link") }}
            </q-item-section>
          </q-item>
          <q-item
            v-ripple
            to="/admin/users"
          >
            <q-item-section avatar>
              <q-icon name="groups" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.user_list") }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>

      <div
        role="presentation"
        class="sidebar-avatar absolute-top"
        style="border-right: 1px solid #3d47ca"
      >
        <avatar-block
          style="padding: 16px;"
          :user="currentUser"
          class="absolute-bottom bg-secondary"
        />
      </div>
    </q-drawer>

    <q-page-container>
      <q-page role="main">
        <email-verification-banner v-if="!currentUser.email_verified_at" />
        <router-view />
      </q-page>
    </q-page-container>
    <app-footer />
  </q-layout>
</template>

<script>
import AppFooter from "../components/AppFooter.vue";
import AppHeader from "src/components/AppHeader.vue";
import AvatarBlock from "src/components/molecules/AvatarBlock.vue";
import { CURRENT_USER } from "src/graphql/queries";
import EmailVerificationBanner from "src/components/molecules/EmailVerificationBanner.vue";
export default {
  name: "MainLayout",
  components: { AppFooter, AppHeader, EmailVerificationBanner, AvatarBlock },
  data: () => {
    return {
      leftDrawerOpen: false
    };
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  }
};
</script>

<style lang="sass">
.sidebar
  $avatar-height: 150px
  .sidebar-avatar
    height: $avatar-height
    background-color: $primary
    color: white
  .sidebar-nav
    height: calc(100% - #{$avatar-height})
    margin-top: $avatar-height
    border-right: 1px solid #ddd
</style>
