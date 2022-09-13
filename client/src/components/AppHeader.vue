<template>
  <input id="locale-switch" v-model="locale" type="hidden" />
  <q-header class="header" @keypress="toggleLocale">
    <q-toolbar class="header-toolbar">
      <div class="q-pa-sm">
        <h1 class="q-ma-none text-h4 site-title">
          Collaborative Community Review
        </h1>
        <small class="site-subtitle">Submission Review System</small>
      </div>
      <q-space />

      <template v-if="currentUser">
        <NotificationPopup />
        <q-btn-dropdown
          stretch
          flat
          data-cy="dropdown_username"
          :label="currentUser.username"
        >
          <q-list
            role="navigation"
            aria-label="Dropdown Navigation"
            data-cy="headerUserMenu"
          >
            <q-item clickable data-cy="link_my_account" to="/account/profile">
              <q-item-section avatar>
                <q-icon name="account_circle" />
              </q-item-section>
              <q-item-section>
                {{ $t("header.account_link") }}
              </q-item-section>
            </q-item>
            <div v-if="isAppAdmin">
              <q-separator />
              <q-item dense>
                <q-item-section>
                  <q-item-label class="text-bold"
                    >Application Administration</q-item-label
                  >
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
    <div class="header-nav">
      <q-list class="row">
        <q-item v-if="currentUser" to="/dashboard">
          <q-item-section side>
            <q-icon name="dashboard" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.dashboard") }}
          </q-item-section>
        </q-item>
        <q-item to="/publications">
          <q-item-section side>
            <q-icon name="collections_bookmark" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.publications") }}
          </q-item-section>
        </q-item>
        <q-item data-cy="submissions_link" to="/submissions">
          <q-item-section side>
            <q-icon name="content_copy" />
          </q-item-section>
          <q-item-section>
            {{ $t("header.submissions") }}
          </q-item-section>
        </q-item>
      </q-list>
    </div>
  </q-header>
</template>

<script setup>
import NotificationPopup from "src/components/molecules/NotificationPopup.vue"
import { useCurrentUser } from "src/use/user"
import { useI18n } from "vue-i18n"
import { useMagicKeys } from "@vueuse/core"
import { watchEffect } from "vue"
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
