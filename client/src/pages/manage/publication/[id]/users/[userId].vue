<template>
  <div class="q-px-lg">
    <nav class="q-pt-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.publications')"
          :to="{ name: 'publication:index' }"
        />
        <q-breadcrumbs-el
          :label="publication?.name ?? ''"
          :to="{ name: 'manage:publication:dashboard', params: { id } }"
        />
        <q-breadcrumbs-el
          :label="$t('publication.manage.users.heading')"
          :to="{ name: 'manage:publication:submitters', params: { id } }"
        />
        <q-breadcrumbs-el :label="user?.name ?? user?.email ?? ''" />
      </q-breadcrumbs>
    </nav>

    <h2 class="q-mt-md q-mb-sm" style="font-size: 1.5rem">
      {{ user?.name ?? user?.email ?? "" }}
    </h2>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationUserDetail($id: ID!, $userId: ID!) {
    publication(id: $id) {
      id
      name
    }
    user(id: $userId) {
      id
      name
      username
      email
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { GetPublicationUserDetailDocument } from "src/graphql/generated/graphql"

interface Props {
  id: string
  userId: string
}
const props = defineProps<Props>()

const { result } = useQuery(GetPublicationUserDetailDocument, {
  id: props.id,
  userId: props.userId
})
const publication = computed(() => result.value?.publication ?? null)
const user = computed(() => result.value?.user ?? null)
</script>
