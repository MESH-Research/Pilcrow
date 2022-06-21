<template>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-pa-md">
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
import { GET_PUBLICATION } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => {
  return result.value?.publication ?? null
})
</script>
