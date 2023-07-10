<template>
  <article class="q-px-md">
    <h2>{{ $t("publication.setup_pages.users") }}</h2>
    <p>
      {{ $t("publication.users") }}
    </p>
    <q-banner
      v-if="publication.publication_admins.length === 0"
      inline-actions
      rounded
      class="highlight"
    >
      <template #avatar>
        <q-icon name="tips_and_updates" size="sm" />
      </template>
      {{ $t("publication.setup_pages.problems.no_admin") }}
    </q-banner>
    <div class="column q-gutter-md q-mb-lg">
      <assigned-publication-users
        data-cy="admins_list"
        role-group="publication_admins"
        :container="publication"
        mutable
      />
      <q-separator />
      <assigned-publication-users
        data-cy="editors_list"
        role-group="editors"
        :container="publication"
        mutable
      />
    </div>
  </article>
</template>

<script setup>
import AssignedPublicationUsers from "src/components/AssignedPublicationUsers.vue"
defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
</script>
