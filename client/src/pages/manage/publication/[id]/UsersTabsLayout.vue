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
          :to="{ name: 'manage:publication:dashboard', params: { id: id } }"
        />
        <q-breadcrumbs-el :label="$t('publication.manage.users.heading')" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-mt-md q-mb-sm" style="font-size: 1.5rem">
      {{ $t("publication.manage.users.heading") }}
    </h2>

    <q-tabs
      :model-value="activeTab"
      active-color="primary"
      indicator-color="primary"
      align="left"
      class="q-mb-md"
      dense
      no-caps
    >
      <q-route-tab
        name="submitters"
        :label="$t('publication.manage.users.tabs.submitters')"
        :to="{
          name: 'manage:publication:submitters',
          params: { id: id }
        }"
      />
      <q-route-tab
        name="team"
        :label="$t('publication.manage.users.tabs.team')"
        :to="{ name: 'manage:publication:team', params: { id: id } }"
      />
      <q-route-tab
        name="invited"
        :to="{ name: 'manage:publication:invited', params: { id: id } }"
      >
        <span class="row items-center no-wrap q-gutter-xs">
          {{ $t("publication.manage.users.tabs.invited") }}
          <q-badge
            v-if="invitedCount > 0"
            color="warning"
            text-color="dark"
            :label="invitedCount"
            :aria-label="
              $t('publication.manage.users.tabs.invited_count', {
                n: invitedCount
              })
            "
          />
        </span>
      </q-route-tab>
    </q-tabs>

    <router-view :id="id" />
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationMeta($id: ID!) {
    publication(id: $id) {
      id
      name
      effective_role
    }
  }
`)

graphql(`
  query GetPublicationUsers(
    $id: ID!
    $page: Int
    $first: Int
    $search: String
    $roles: [SubmissionUserRoles!]!
    $staged: Boolean
    $orderBy: [QueryPublicationUsersOrderByOrderByClause!]
  ) {
    publication(id: $id) {
      id
      users(
        page: $page
        first: $first
        search: $search
        roles: $roles
        staged: $staged
        orderBy: $orderBy
      ) {
        ...QueryTable
        data {
          id
          ...NameAvatarCell
          email
          as_submitter_count
          as_reviewer_active_count
          as_reviewer_completed_count
          as_coordinator_active_count
          as_coordinator_completed_count
        }
      }
    }
  }
`)

graphql(`
  query GetPublicationInvitedCount($id: ID!) {
    publication(id: $id) {
      id
      users(first: 1, roles: [reviewer, review_coordinator], staged: true) {
        paginatorInfo {
          total
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, watch } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useRoute, useRouter } from "vue-router"
import {
  GetPublicationMetaDocument,
  GetPublicationInvitedCountDocument
} from "src/graphql/generated/graphql"

interface Props {
  id: string
}
const props = defineProps<Props>()

const route = useRoute()
const router = useRouter()

// Which tab is active (purely for the visual indicator — actual
// navigation is handled by q-route-tab + nested routes).
const activeTab = computed(() => {
  const name = route.name?.toString() ?? ""
  if (name.endsWith(":invited")) return "invited"
  if (name.endsWith(":team")) return "team"
  return "submitters"
})

// Publication meta for breadcrumbs + access redirect.
const { result } = useQuery(GetPublicationMetaDocument, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)

watch(publication, (pub) => {
  if (
    pub &&
    pub.effective_role !== "publication_admin" &&
    pub.effective_role !== "editor"
  ) {
    router.replace("/error403")
  }
})

// Outstanding invitation count — drives the tab badge.
const { result: invitedCountResult } = useQuery(
  GetPublicationInvitedCountDocument,
  { id: props.id }
)
const invitedCount = computed(
  () => invitedCountResult.value?.publication?.users?.paginatorInfo?.total ?? 0
)
</script>
