<template>
  <router-view />
</template>

<script setup lang="ts">
definePage({
  redirect: { name: "publication:index" },
  meta: {
    crumb: {
      label: "Publications",
      to: { name: "publication:index" }
    }
  }
})
</script>
