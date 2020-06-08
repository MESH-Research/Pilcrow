<template>
  <q-layout view="hHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          @click="leftDrawerOpen = !leftDrawerOpen"
          icon="menu"
          aria-label="Menu"
        />

        <q-toolbar-title>
          Collaborative Community Review (CCR)
        </q-toolbar-title>
        <template v-if="isLoggedIn">
          <q-btn-dropdown stretch flat :label="user.username">
            <q-list>
              <q-item-label header>User Account</q-item-label>
              <q-item clickable @click="logout()">
                <q-item-section avatar>
                  <q-icon name="mdi-logout" />
                </q-item-section>
                <q-item-section>
                  Logout
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </template>
        <template v-else>
          <q-btn label="Register" to="/register" stretch flat />
          <q-separator vertical dark />
          <q-btn label="Login" to="/login" stretch flat />
        </template>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" bordered content-class="bg-grey-1">
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
export default {
  name: "MainLayout",

  components: {},

  data() {
    return {
      leftDrawerOpen: false
    };
  },
  computed: mapGetters("auth/", ["isLoggedIn", "user"]),
  methods: mapActions("auth/", ["logout"])
};
</script>
