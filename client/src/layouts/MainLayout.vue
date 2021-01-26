<template>
  <q-layout view="lhh lpr lFf">
    <app-header />
    <q-drawer v-model="leftDrawerOpen" show-if-above content-class="bg-grey-1">
      <div class="drawer-header">Stuff To Do</div>
    </q-drawer>

    <q-page-container>
      <q-page>
        <router-view />
      </q-page>
    </q-page-container>
    <app-footer />
  </q-layout>
</template>

<script>
import AppFooter from "../components/AppFooter.vue";
import AppHeader from "src/components/AppHeader.vue";
export default {
  name: "MainLayout",

  components: { AppFooter, AppHeader },

  data: () => {
    return {
      leftDrawerOpen: false
    };
  },
};
</script>

<style lang="scss">
.drawer-header {
  height: 143px;
  background: $primary;
}
</style>
