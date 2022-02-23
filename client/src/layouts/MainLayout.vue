<template>
  <q-layout view="lhh lpr lFf">
    <app-header v-model="leftDrawerOpen" drawer />
    <q-drawer
      v-if="currentUser"
      id="sidebar"
      v-model="leftDrawerOpen"
      show-if-above
      role="navigation"
      aria-label="Main Navigation"
      content-class="sidebar bg-grey-1"
    >
      <div
        class="bg-secondary row items-center text-white"
        style="height: 70px"
      >
        <avatar-block :user="currentUser" />
      </div>
      <q-scroll-area class="sidebar-nav">
        <q-list>
          <q-item v-ripple to="/dashboard">
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.dashboard") }}
            </q-item-section>
          </q-item>

          <q-item v-ripple to="/admin/users">
            <q-item-section avatar>
              <q-icon name="groups" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.user_list") }}
            </q-item-section>
          </q-item>
          <q-item v-ripple to="/admin/publications">
            <q-item-section avatar>
              <q-icon name="collections_bookmark" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.publications") }}
            </q-item-section>
          </q-item>
          <q-item v-ripple data-cy="submissions_link" to="/submissions">
            <q-item-section avatar>
              <q-icon name="content_copy" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.submissions") }}
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>
    </q-drawer>

    <q-page-container>
      <q-page role="main">
        <email-verification-banner v-if="!currentUser?.email_verified_at" />
        <router-view />
      </q-page>
    </q-page-container>
    <app-footer />
  </q-layout>
</template>

<script setup>
import AppFooter from "../components/AppFooter.vue"
import AppHeader from "src/components/AppHeader.vue"
import AvatarBlock from "src/components/molecules/AvatarBlock.vue"
import EmailVerificationBanner from "src/components/molecules/EmailVerificationBanner.vue"

import { useCurrentUser } from "src/use/user"
import { ref } from "vue"

const leftDrawerOpen = ref(false)
const { currentUser } = useCurrentUser()
</script>

<style lang="sass">
#sidebar
  $avatar-height: 70px
  .sidebar-avatar
    height: $avatar-height
  .sidebar-nav
    height: calc(100% - #{$avatar-height})
    border-right: 1px solid #ddd
</style>
