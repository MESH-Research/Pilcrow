<template>
  <nav class="nav q-px-lg q-pt-md q-gutter-sm">
    <div class="breadcrumb-wrap">
      <router-link to="/" class="breadcrumb">
        <q-icon name="home" />
        Home
      </router-link>
    </div>
    <div
      v-for="crumb in crumbs.slice(0, -1)"
      :key="`crumb-${crumb.label}`"
      class="breadcrumb-wrap"
    >
      <q-icon size="1.5em" name="chevron_right" color="primary" class="sep" />
      <router-link :to="crumb.to" class="breadcrumb">
        <q-icon :name="crumb.icon" v-if="crumb.icon" />
        {{ unref(crumb.label.value) }}
      </router-link>
    </div>
    <div v-if="last" class="breadcrumb-wrap">
      <q-icon size="1.5em" name="chevron_right" color="primary" class="sep" />
      <a>
        <q-icon :name="last.icon" v-if="last.icon" />
        {{ unref(last.label.value) }}
      </a>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { useCrumbs } from "src/use/breadcrumbs"
import { unref } from "vue"

const { crumbs } = useCrumbs()
const last = computed(() => crumbs.value.slice(-1)[0])
</script>

<style scoped lang="scss">
// css variables

* {
  box-sizing: border-box;
}

:deep(a, a:visited) {
  text-decoration: none;
  color: $primary;
  transition:
    color ease-in-out 0.3s,
    background ease-in-out 0.2s;
  font-size: 1.1em;
}

.nav {
  display: flex;
  white-space: nowrap;
  overflow: hidden;
}

/// BREADCRUMBS ///

.sep {
  padding: 0 5px;
}
</style>
