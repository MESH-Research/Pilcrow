<template>
  <router-view />
</template>

<script setup lang="ts">
// Layout wrapper for everything under /admin. Holds the shared admin-area
// access gate and the root "Administration" breadcrumb so children only have
// to declare their own leaf crumb. The `adminArea` gate admits anyone holding
// any global `admin_*` ability — the union of admin capabilities, not a single
// "is admin" flag — so new admin abilities extend access automatically.
definePage({
  meta: {
    gate: "adminArea",
    crumb: {
      label: "breadcrumbs.admin.administration",
      to: { name: "admin:dashboard" }
    }
  }
})
</script>
