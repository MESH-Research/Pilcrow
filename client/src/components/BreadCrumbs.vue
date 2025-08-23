<template>
  <nav class="nav">
    <div class="breadcrumb-wrap">
      <router-link to="/" class="breadcrumb">
        <q-icon name="home" />
        Home
      </router-link>
    </div>
    <div
      v-for="(crumb, idx) in crumbs.slice(0, -1)"
      v-bind="crumb"
      :key="`crumb${idx}`"
      class="breadcrumb-wrap"
    >
      <q-icon size="1.5em" name="chevron_right" color="primary" class="sep" />
      <router-link :to="crumb.to" class="breadcrumb">
        <q-icon :name="crumb.icon" />
        {{ unref(crumb.label.value) }}
      </router-link>
    </div>
    <div v-if="last" class="breadcrumb-wrap">
      <q-icon size="1.5em" name="chevron_right" color="primary" class="sep" />
      <a>{{ unref(last.label.value) }}</a>
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

.breadcrumb-wrap {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 0 auto;
  flex-shrink: 1000;
  display: inline-block;
  transition: transform ease-in-out 0.3s;

  &:hover {
    flex: 1 0 auto;
    transition: transform ease-in-out 0.4s;
  }

  //First Breadcrumb
  &:first-child {
    flex: 0 0 auto;
    flex-shrink: 0.5;
  }

  //Last Breadcrumb
  &:last-child {
    flex: 1 0 auto !important;
    font-weight: normal;
    :deep(a) {
      color: black;
    }
  }
}

.breadcrumb {
  flex: 0 1 auto;
}
.sep {
  padding: 0 5px;
}
</style>
