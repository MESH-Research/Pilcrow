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
      content-class="sidebar bg-grey-1"
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
          <q-item
            v-ripple
            to="/admin/publications"
          >
            <q-item-section avatar>
              <q-icon name="collections_bookmark" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.publications") }}
            </q-item-section>
          </q-item>
          <q-item
            v-ripple
            to="/submissions"
          >
            <q-item-section avatar>
              <q-icon name="content_copy" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.submissions") }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>

      <q-img
        role="presentation"
        class="sidebar-avatar absolute-top"
        src="https://cdn.quasar.dev/img/material.png"
      >
        <avatar-block
          :user="currentUser"
          class="absolute-bottom bg-secondary"
        />
      </q-img>
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
  .sidebar-nav
    height: calc(100% - #{$avatar-height})
    margin-top: $avatar-height
    border-right: 1px solid #ddd
</style>
