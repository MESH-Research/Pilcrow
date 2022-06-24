<template>
  <article class="q-px-md">
    <h2>Users</h2>
    <p>
      Assigned users have special permissions on your publication. A user cannot
      be both an admin and a editor at the same time.
    </p>
    <q-banner
      v-if="publication.publication_admins.length === 0"
      inline-actions
      rounded
      class="bg-yellow-2 text-black"
    >
      <template #avatar>
        <q-icon name="tips_and_updates" />
      </template>
      This publication has no publication administrators assigned and can only
      be administered by application admins.
    </q-banner>
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
