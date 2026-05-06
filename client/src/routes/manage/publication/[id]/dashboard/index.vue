<template>
  <router-view />
</template>

<script setup lang="ts">
definePage({
  redirect: { name: "manage:publication:submissions" }
})
</script>
