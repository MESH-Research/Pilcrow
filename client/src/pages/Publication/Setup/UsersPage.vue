<template>
  <article class="q-px-md">
    <h2>Users</h2>
    <p>Assigned users have special permissions on your publication.</p>
    <div class="column q-gutter-md">
      <assigned-users
        data-cy="admins_list"
        relationship="publication_admins"
        :container="publication"
        mutable
      />
      <q-separator />
      <assigned-users
        data-cy="editors_list"
        relationship="editors"
        :container="publication"
        mutable
      />
    </div>
  </article>
</template>

<script setup>
import AssignedUsers from "src/components/AssignedUsersComponent.vue"
defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
</script>
