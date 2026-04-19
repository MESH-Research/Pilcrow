<template>
  <router-view />
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetManagedPublication($id: ID!) {
    publication(id: $id) {
      id
      name
      effective_role
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, watch } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useRoute, useRouter } from "vue-router"
import { GetManagedPublicationDocument } from "src/graphql/generated/graphql"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:id",
  meta: {
    crumb: {
      label: "Publication",
      to: { name: "manage:publication:dashboard" }
    }
  }
})

const route = useRoute("manage:publication:id")
const router = useRouter()

const { result } = useQuery(GetManagedPublicationDocument, () => ({
  id: route.params.id as string
}))
const publication = computed(() => result.value?.publication ?? null)

setCrumbLabel(
  "manage:publication:id",
  computed(() => publication.value?.name)
)

watch(publication, (pub) => {
  if (
    pub &&
    pub.effective_role !== "publication_admin" &&
    pub.effective_role !== "editor"
  ) {
    router.replace("/error403")
  }
})
</script>
