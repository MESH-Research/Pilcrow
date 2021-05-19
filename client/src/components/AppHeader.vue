<template>
  <q-header class="header">
    <q-toolbar>
      <q-btn
        v-if="value !== null"
        flat
        round
        dense
        icon="menu"
        :aria-label="$t('header.menu_button_aria')"
        aria-controls="sidebar"
        :aria-expanded="value.toString()"
        @click="$emit('input', !value)"
      />
      <q-space />

      <template v-if="currentUser">
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
            <q-item
              clickable
              to="/dashboard"
            >
              <q-item-section avatar>
                <q-icon name="dashboard" />
              </q-item-section>
              <q-item-section>{{ $t("header.dashboard") }}</q-item-section>
            </q-item>
            <q-item
              clickable
              data-cy="link_my_account"
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
              clickable
              data-cy="link_all_users"
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
              clickable
              to="/admin/publications"
            >
              <q-item-section avatar>
                <q-icon name="collections_bookmark" />
              </q-item-section>
              <q-item-section>
                {{ $t("header.publications") }}
              </q-item-section>
            </q-item>
            <q-separator />
            <q-item
              clickable
              @click="logout"
            >
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
        <q-btn
          :label="$t('auth.register')"
          to="/register"
          stretch
          flat
        />
        <q-separator
          vertical
          dark
        />
        <q-btn
          :label="$t('auth.login')"
          to="/login"
          stretch
          flat
        />
      </template>
    </q-toolbar>
    <div class="title">
      <h1 class="q-ma-none">
        Collaborative Community Review
      </h1>
      <div class="text-subtitle">
        Submission Review System
      </div>
    </div>
  </q-header>
</template>

<script>
import appAuth from "src/components/mixins/appAuth";
import { CURRENT_USER } from "src/graphql/queries";

export default {
  name: "AppHeader",
  mixins: [appAuth],
  props: {
    //Drawer status
    value: {
      type: Boolean,
      default: null
    }
  },
  data() {
    return {
      currentUser: null,
      drawerShowing: false
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
.header
  height: 150px
  overflow: hidden
  .title
    padding: 24px 48px 8px 48px
  .header-image
    height: 100%
    z-index: -1
    opacity: 0.2
    filter: grayscale(2%)

@media (max-width: $breakpoint-xs)
  .header
    height: auto
    padding-bottom: 10px
    .title
      padding: 0px 15px 5px 15px
      .text-h4
        font-size: 1.3rem
</style>
