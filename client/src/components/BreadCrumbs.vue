<template>
  <nav v-if="crumbs.length" aria-label="Breadcrumb">
    <q-breadcrumbs>
      <q-breadcrumbs-el
        v-for="(crumb, idx) in head"
        :key="idx"
        :to="crumb.to"
        :icon="crumb.icon"
        :label="crumb.label.value"
      />
      <q-breadcrumbs-el
        v-if="last"
        :icon="last.icon"
        :label="last.label.value"
      />
    </q-breadcrumbs>
  </nav>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useCrumbs } from "src/use/breadcrumbs"

const { crumbs } = useCrumbs()

const head = computed(() => crumbs.value.slice(0, -1))
const last = computed(() => crumbs.value.slice(-1)[0])
</script>
