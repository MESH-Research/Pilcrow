<template>
  <q-badge
    :color="role === 'publication_admin' ? 'primary' : 'secondary'"
    text-color="white"
    class="role-badge"
  >
    {{ $t(`publication.manage.dashboard.role.${role}`) }}
  </q-badge>
</template>

<script setup lang="ts">
interface Props {
  /** Publication role — publication_admin | editor. */
  role: string
}
defineProps<Props>()
</script>

<style scoped>
/* Filled badge (not outline) so contrast works in both light and
   dark modes — outlined badges' border + same-color text don't
   reliably clear WCAG contrast against dark surfaces. */
.role-badge {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  flex: 0 0 auto;
}
</style>
