<template>
  <q-layout view="lhh lpr lff">
    <AppHeader />
    <AppFooter />

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script lang="ts" setup>
import AppFooter from "src/components/AppFooter.vue"
import AppHeader from "src/components/AppHeader.vue"

defineComponent({
  name: "App"
})
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
