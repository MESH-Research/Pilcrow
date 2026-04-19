<template>
  <div class="q-px-lg q-pt-md">
    <h2 class="q-mt-md q-mb-sm" style="font-size: 1.5rem">
      {{ user?.name ?? user?.email ?? "" }}
    </h2>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationUserDetail($userId: ID!) {
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
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:user",
  props: true,
  meta: {
    crumb: {
      label: "User"
    }
  }
})

interface Props {
  id: string
  userId: string
}
const props = defineProps<Props>()

const { result } = useQuery(GetPublicationUserDetailDocument, {
  userId: props.userId
})
const user = computed(() => result.value?.user ?? null)

setCrumbLabel(
  "manage:publication:user",
  computed(() => user.value?.name ?? user.value?.email ?? undefined)
)
</script>
