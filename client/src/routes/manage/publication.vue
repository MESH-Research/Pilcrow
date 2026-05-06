<template>
  <router-view />
</template>

<script setup lang="ts">
definePage({
  redirect: { name: "manage:index" },
  meta: {
    crumb: {
      label: "Manage",
      to: { name: "manage:index" }
    }
  }
})
</script>
