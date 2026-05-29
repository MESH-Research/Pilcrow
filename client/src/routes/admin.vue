<template>
  <router-view />
</template>

<script setup lang="ts">
// Layout wrapper for everything under /admin. Holds the shared
// `requiresAppAdmin` gate and the root "Administration" breadcrumb
// so children only have to declare their own leaf crumb.
definePage({
  meta: {
    requiresAppAdmin: true,
    crumb: {
      label: "breadcrumbs.admin.administration",
      to: { name: "admin:dashboard" }
    }
  }
})
</script>
