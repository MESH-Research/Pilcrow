<template>
  <article class="q-pa-md">
    <div class="column q-gutter-md">
      <assigned-users
        data-cy="admins_list"
        relationship="publication_admins"
        :container="publication"
        mutable
      />
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
