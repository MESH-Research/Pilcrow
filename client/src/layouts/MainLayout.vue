<template>
  <q-layout view="lhh lpr lff">
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
      <div class="sidebar-nav column justify-between">
        <q-list>
          <q-item to="/dashboard">
            <q-item-section avatar>
              <q-icon name="dashboard" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.dashboard") }}
            </q-item-section>
          </q-item>
          <q-item to="/publications">
            <q-item-section avatar>
              <q-icon name="collections_bookmark" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.publications") }}
            </q-item-section>
          </q-item>
          <q-item data-cy="submissions_link" to="/submissions">
            <q-item-section avatar>
              <q-icon name="content_copy" />
            </q-item-section>
            <q-item-section>
              {{ $t("header.submissions") }}
            </q-item-section>
          </q-item>
        </q-list>
        <q-list>
          <q-expansion-item
            expand-separator
            icon="settings"
            :label="$t('header.application_administration')"
          >
            <q-list class="submenu">
              <q-item to="/admin/users">
                <q-item-section avatar>
                  <q-icon name="groups" />
                </q-item-section>
                <q-item-section>
                  {{ $t("header.user_list") }}
                </q-item-section>
              </q-item>
            </q-list>
          </q-expansion-item>
        </q-list>
      </div>
    </q-drawer>

    <q-page-container>
      <q-page role="main">
        <email-verification-banner v-if="!currentUser?.email_verified_at" />
        <router-view />
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
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
  .submenu .q-item
    padding-left: 40px
</style>
