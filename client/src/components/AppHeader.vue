<template>
  <q-header class="header">
    <q-toolbar>
      <q-btn
        v-if="props.modelValue !== null"
        flat
        round
        dense
        icon="menu"
        :aria-label="$t('header.menu_button_aria')"
        aria-controls="sidebar"
        :aria-expanded="(!!props.modelValue).toString()"
        @click="toggleDrawer"
      />
      <div class="q-pa-md">
        <h1 class="q-ma-none text-h4" style="line-height: 1">
          Collaborative Community Review
        </h1>
        <small>Submission Review System</small>
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
            <q-item clickable to="/dashboard">
              <q-item-section avatar>
                <q-icon name="dashboard" />
              </q-item-section>
              <q-item-section>{{ $t("header.dashboard") }}</q-item-section>
            </q-item>
            <q-item clickable data-cy="link_my_account" to="/account/profile">
              <q-item-section avatar>
                <q-icon name="account_circle" />
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
  </q-header>
</template>

<script setup>
import NotificationPopup from "src/components/molecules/NotificationPopup.vue"
import { useLogout, useCurrentUser } from "src/use/user"

const props = defineProps({
  //Drawer status
  modelValue: {
    type: Boolean,
    default: null,
  },
})
const emit = defineEmits(["update:modelValue"])

const { currentUser } = useCurrentUser()
const { logoutUser: logout } = useLogout()

function toggleDrawer() {
  emit("update:modelValue", !props.modelValue)
}
</script>

<style lang="sass">
.site-title a
  text-decoration: none
  color: white
.header
  height:70px
  overflow: hidden
  .header-image
    height: 100%
    z-index: -1
    opacity: 0.2
    filter: grayscale(2%)

@media (max-width: $breakpoint-xs)
  .header
    height: auto
    padding-bottom: 10px
</style>
