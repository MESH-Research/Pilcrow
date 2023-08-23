<template>
  <input id="locale-switch" v-model="locale" type="hidden" />
  <q-header class="header" @keypress="toggleLocale">
    <app-banner />
    <q-toolbar class="header-toolbar">
      <router-link
        to="/"
        class="q-pa-sm row q-gutter-md items-center text-white"
        style="text-decoration: unset"
      >
        <q-img
          :alt="$t('header.logo_alt')"
          src="/logo-100x100.png"
          style="width: 50px; height: 50px"
        />
        <div v-if="$q.screen.width >= 430" class="column">
          <h1 class="q-ma-none text-h4 site-title">
            {{ $t("header.site_title") }}
          </h1>
          <small class="site-subtitle text-body1">{{
            $t("header.site_subtitle")
          }}</small>
        </div>
      </router-link>
      <q-space />

      <template v-if="currentUser">
        <NotificationPopup />
        <q-btn-dropdown
          stretch
          flat
          data-cy="dropdown_username"
          :aria-label="$t('header.account_btn_aria')"
        >
          <template #label>
            <q-icon name="account_circle" class="lt-md" />
            <span class="gt-sm">{{ currentUser.username }}</span>
          </template>
          <q-list
            role="navigation"
            :aria-label="$t('header.account_dropdown_aria')"
            data-cy="headerUserMenu"
          >
            <q-item clickable to="/account/metadata">
              <q-item-section avatar>
                <q-icon name="account_circle" />
              </q-item-section>
              <q-item-section>
                {{ $t("header.profile") }}
              </q-item-section>
            </q-item>
            <q-item clickable data-cy="link_my_account" to="/account/profile">
              <q-item-section avatar>
                <q-icon name="o_settings" />
              </q-item-section>
              <q-item-section>
                {{ $t("header.login_and_password") }}
              </q-item-section>
            </q-item>
            <div v-if="isAppAdmin">
              <q-separator />
              <q-item dense>
                <q-item-section>
                  <q-item-label class="text-bold">
                    {{ $t("header.application_administration") }}
                  </q-item-label>
                </q-item-section>
              </q-item>
              <q-item to="/admin/users">
                <q-item-section avatar>
                  <q-icon name="groups" />
                </q-item-section>
                <q-item-section>
                  {{ $t("header.user_list") }}
                </q-item-section>
              </q-item>
              <q-item :to="{ name: 'admin:publication:index' }">
                <q-item-section avatar>
                  <q-icon name="collections_bookmark" />
                </q-item-section>
                <q-item-section>
                  {{ $t("header.publications") }}
                </q-item-section>
              </q-item>
            </div>
            <q-separator />
            <q-item to="/logout">
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
    <div v-if="currentUser" class="header-nav">
      <q-list
        class="row"
        role="navigation"
        :aria-label="$t('header.nav_aria_label')"
      >
        <q-item
          role="link"
          to="/dashboard"
          :class="`${$q.screen.width < 500 ? 'q-px-sm' : ''}`"
        >
          <q-item-section side class="gt-xs">
            <q-icon class="gt-xs" name="dashboard" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.dashboard") }}
          </q-item-section>
        </q-item>
        <q-item
          to="/publications"
          role="link"
          :class="`${$q.screen.width < 500 ? 'q-px-sm' : ''}`"
        >
          <q-item-section side class="gt-xs">
            <q-icon name="collections_bookmark" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.publications") }}
          </q-item-section>
        </q-item>
        <q-item
          data-cy="submissions_link"
          to="/submissions"
          role="link"
          :class="`${$q.screen.width < 500 ? 'q-px-sm' : ''}`"
        >
          <q-item-section side class="gt-xs">
            <q-icon name="content_copy" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.submissions") }}
          </q-item-section>
        </q-item>
        <q-item
          data-cy="reviews_link"
          to="/reviews"
          role="link"
          :class="`${$q.screen.width < 500 ? 'q-px-sm' : ''}`"
        >
          <q-item-section side class="gt-xs">
            <q-icon class="material-icons-outlined" name="rate_review" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.reviews") }}
          </q-item-section>
        </q-item>
      </q-list>
    </div>
  </q-header>
</template>

<script setup>
import { useMagicKeys } from "@vueuse/core"
import NotificationPopup from "src/components/molecules/NotificationPopup.vue"
import { useCurrentUser } from "src/use/user"
import { watchEffect } from "vue"
import { useI18n } from "vue-i18n"
import AppBanner from "./AppBanner.vue"

defineProps({
  //Drawer status
  modelValue: {
    type: Boolean,
    default: null,
  },
})

const { currentUser, isAppAdmin } = useCurrentUser()
const { locale } = useI18n({ useScope: "global" })
const { ctrl, shift, alt, t } = useMagicKeys()

watchEffect(() => {
  if (ctrl.value && shift.value && alt.value && t.value) {
    toggleLocale()
  }
})
function toggleLocale() {
  if (locale.value == "en-US") {
    locale.value = "copy"
  } else {
    locale.value = "en-US"
  }
}
</script>

<style lang="sass">
.header-toolbar
  height: 70px
  overflow: hidden
.site-title
  line-height: 1
.header-nav
  background: $secondary
.header-nav .q-item__section--side
  color: inherit
</style>
