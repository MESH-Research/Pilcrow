<template>
  <nav v-if="crumbs.length" aria-label="Breadcrumb">
    <q-breadcrumbs>
      <q-breadcrumbs-el
        v-for="(crumb, idx) in head"
        :key="idx"
        :to="crumb.to"
        :icon="crumb.icon"
      >
        <span class="crumb-label" :title="crumb.label.value">
          {{ crumb.label.value }}
        </span>
      </q-breadcrumbs-el>
      <q-breadcrumbs-el v-if="last" :icon="last.icon">
        <span class="crumb-label" :title="last.label.value">
          {{ last.label.value }}
        </span>
      </q-breadcrumbs-el>
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

<style scoped>
/* Long submission/publication titles in the trail used to wrap onto
   a second line and balloon the page header. Cap each crumb's text
   width and ellipsize; the full text is exposed via the tooltip
   `title` attribute for cases where the truncated form is ambiguous. */
.crumb-label {
  display: inline-block;
  max-width: 22ch;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  vertical-align: bottom;
}
</style>
