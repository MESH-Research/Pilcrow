<template>
  <q-layout view="lhh lpr lFf">
    <q-header>
      <q-toolbar>
        <q-btn
          flat
          dense
          round
          @click="leftDrawerOpen = !leftDrawerOpen"
          icon="menu"
          aria-label="Menu"
        />
        <q-space />

        <template v-if="isLoggedIn">
          <q-btn-dropdown stretch flat :label="user.username">
            <q-list>
              <q-item clickable to="/account/profile">
                <q-item-section avatar> </q-item-section>
                <q-item-section>
                  {{ $t("header.account_link") }}
                </q-item-section>
              </q-item>
              <q-separator />
              <q-item clickable @click="logout()">
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
      <div class="q-px-xl q-pt-lg q-pb-sm">
        <div class="text-h4 text-weight-regular site-title">
          <router-link to="/">
            <span class="text-weight-medium">Public</span> Philosophy Journal
            <strong>Quarterly</strong>
          </router-link>
        </div>
        <div class="text-subtitle">Submission & Review System</div>
      </div>
      <q-img src="statics/header-back.jpg" class="header-image absolute-top" />
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above content-class="bg-grey-1">
      <div class="drawer-header"></div>
    </q-drawer>

    <app-footer />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import AppFooter from "../components/AppFooter.vue";
export default {
  name: "MainLayout",

  components: { AppFooter },

  data() {
    return {
      leftDrawerOpen: false
    };
  },
  computed: mapGetters("auth/", ["isLoggedIn", "user"]),
  methods: {
    logout() {
      this.$store.dispatch("auth/logout").then(() => {
        this.$router.push("/");
      });
    }
  },
  preFetch({ store, redirect, currentRoute }) {
    if (!store.getters["auth/isLoggedIn"]) {
      console.log();
      redirect(`/login?redirect=${encodeURIComponent(currentRoute.path)}`);
    }
  }
};
</script>

<style lang="scss">
.header-image {
  height: 100%;
  z-index: -1;
  opacity: 0.2;
  filter: grayscale(2%);
}
.drawer-header {
  height: 143px;
  background: $primary;
}
.site-title a {
  text-decoration: none;
  color: white;
}
</style>