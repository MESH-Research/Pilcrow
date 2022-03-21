<template>
  <q-header class="header">
    <q-toolbar class="header-toolbar">
      <q-btn
        v-if="props.modelValue !== null"
        data-cy="sidebar_toggle"
        flat
        round
        dense
        icon="switch_right"
        :aria-label="$t('header.menu_button_aria')"
        aria-controls="sidebar"
        :aria-expanded="(!!props.modelValue).toString()"
        @click="toggleDrawer"
      />
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
  </q-header>
</template>

<script setup>
import NotificationPopup from "src/components/molecules/NotificationPopup.vue"
import { useCurrentUser } from "src/use/user"

const props = defineProps({
  //Drawer status
  modelValue: {
    type: Boolean,
    default: null,
  },
})
const emit = defineEmits(["update:modelValue"])

const { currentUser } = useCurrentUser()

function toggleDrawer() {
  emit("update:modelValue", !props.modelValue)
}
</script>

<style lang="sass">
.header-toolbar
  height: 70px
  overflow: hidden
.site-title
  line-height: 1
</style>
