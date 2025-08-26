<template><router-view /></template>
<script lang="ts" setup>
definePage({
  name: "publication:",
  meta: {
    crumb: {
      label: "Publication Name",
      icon: "article",
      to: { name: "publication:home" }
    }
  }
})
</script>
