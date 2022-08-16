<template>
  <q-layout view="lhh lpr lff">
    <app-header />

    <app-footer />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import AppFooter from "../components/AppFooter.vue"
import AppHeader from "src/components/AppHeader.vue"
export default {
  name: "MainLayout",

  components: { AppFooter, AppHeader },

  data() {
    return {
      leftDrawerOpen: false,
    }
  },
}
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
</style>
