<template>
  <router-view />
</template>

<script setup lang="ts">
definePage({
  redirect: { name: "publication:index" },
  meta: {
    crumb: {
      // TODO: point at a dedicated admin landing page once one exists;
      // for now the crumb redirects to the publications index.
      label: "Manage",
      to: { name: "publication:index" }
    }
  }
})
</script>
