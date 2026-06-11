<template>
  <router-view />
</template>

<script setup lang="ts">
// Passthrough layout for the Users admin section. It groups the list
// (users/index.vue) and the per-user detail (users/[id].vue) under one
// folder so the detail route is nested *inside* the section rather than
// being a sibling of the dashboard tiles.
//
// It carries the section-level metadata:
//  - `navigation` → the admin dashboard reads this via childrenOf to
//    render the Users tile (the detail page, lacking `navigation`, never
//    appears as its own tile).
//  - `crumb` → the "Users" breadcrumb, linking to the list; child routes
//    only add their own leaf crumb on top.
//
// Named so childrenOf can resolve it by name; navigating to this name
// renders the index (list) child.
definePage({
  name: "admin:users-section",
  meta: {
    navigation: {
      label: "header.user_list",
      icon: "groups",
      description: "admin.dashboard.users_description",
      order: 10
    },
    crumb: { label: "breadcrumbs.admin.users", to: { name: "admin:users" } }
  }
})
</script>
