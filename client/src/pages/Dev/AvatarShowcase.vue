<template>
  <div class="q-pa-lg">
    <q-banner class="bg-warning text-dark q-mb-md">
      <template #avatar><q-icon name="construction" /></template>
      Dev showcase page. Remove before production.
    </q-banner>

    <h1 class="text-h4 q-mt-none">Avatar / username rendering</h1>

    <p class="text-body2 text-grey-8">
      All the ways user identity is rendered across the app, shown side by side
      against the same sample users. Users without an uploaded avatar and the
      current user are filtered out so the report trigger overlay is always
      visible.
    </p>

    <div v-if="loading" class="q-py-xl row justify-center">
      <q-spinner size="48px" />
    </div>

    <template v-else>
      <section class="q-mb-xl">
        <h2 class="text-h6">AvatarImage (atom) — at various sizes</h2>
        <p class="text-caption text-grey-7">
          Plain avatar. Circle by default, rounded-square when `rounded` is
          passed. Jdenticon fallback renders when the user has no uploaded
          avatar.
        </p>
        <div
          v-for="user in users"
          :key="'ai-' + user.id"
          class="row items-center q-gutter-md q-mb-sm"
        >
          <avatar-image :user="user" size="24px" />
          <avatar-image :user="user" size="30px" />
          <avatar-image :user="user" size="48px" />
          <avatar-image :user="user" size="80px" />
          <avatar-image :user="user" rounded size="48px" />
          <span class="text-caption text-grey-7">{{ user.email }}</span>
        </div>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">ReportableAvatar (molecule)</h2>
        <p class="text-caption text-grey-7">
          Hover to reveal the report trigger overlay. Self-hides on your own
          avatar. <code>compact</code> variant used in comment headers is on the
          right.
        </p>
        <q-markup-table flat bordered dense>
          <thead>
            <tr>
              <th class="text-left">User</th>
              <th>default (48px)</th>
              <th>rounded (48px)</th>
              <th>compact (30px)</th>
              <th>large (80px)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="'ra-' + user.id">
              <td class="text-left">{{ user.email }}</td>
              <td>
                <reportable-avatar :user="user" size="48px" />
              </td>
              <td>
                <reportable-avatar :user="user" rounded size="48px" />
              </td>
              <td>
                <reportable-avatar :user="user" compact round size="30px" />
              </td>
              <td>
                <reportable-avatar :user="user" rounded size="80px" />
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">AvatarBlock (molecule)</h2>
        <p class="text-caption text-grey-7">
          Used in the account profile layout.
        </p>
        <avatar-block
          v-for="user in users"
          :key="'ab-' + user.id"
          :user="user"
          avatar-size="64px"
        />
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">UserListItem (atom)</h2>
        <p class="text-caption text-grey-7">
          Used in reviewer / participant lists and search results.
        </p>
        <q-list bordered separator style="max-width: 480px">
          <user-list-item
            v-for="user in users"
            :key="'uli-' + user.id"
            :user="user as unknown as UserType"
          />
        </q-list>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">NameAvatarCell (table cell)</h2>
        <p class="text-caption text-grey-7">
          Used in admin QueryTable rows (e.g. /admin/users).
        </p>
        <q-markup-table flat bordered dense>
          <thead>
            <tr>
              <th class="text-left">Name</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="'nac-' + user.id">
              <name-avatar-cell :scope="fakeScope(user)" />
            </tr>
          </tbody>
        </q-markup-table>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">CommentHeader (atom)</h2>
        <p class="text-caption text-grey-7">
          Inside a comment. Uses the <code>compact</code> ReportableAvatar
          variant (30px avatar).
        </p>
        <q-card flat bordered class="q-mb-sm">
          <comment-header
            v-for="user in users"
            :key="'ch-' + user.id"
            :comment="fakeComment(user) as unknown as CommentType"
          />
        </q-card>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">Display label helpers</h2>
        <p class="text-caption text-grey-7">
          <code>display_label</code> and <code>@username</code> as used in plain
          text contexts.
        </p>
        <q-list bordered separator style="max-width: 480px">
          <q-item v-for="user in users" :key="'dl-' + user.id">
            <q-item-section>
              <q-item-label>{{ user.name ?? user.username }}</q-item-label>
              <q-item-label caption>@{{ user.username }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <code>#{{ user.id }}</code>
            </q-item-section>
          </q-item>
        </q-list>
      </section>
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetAvatarShowcaseUsers {
    users(first: 50, page: 1) {
      data {
        id
        name
        username
        email
        display_label
        ...avatarImage
        ...avatarBlock
        ...NameAvatarCell
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useCurrentUser } from "src/use/user"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import AvatarBlock from "src/components/molecules/AvatarBlock.vue"
import ReportableAvatar from "src/components/molecules/ReportableAvatar.vue"
import UserListItem from "src/components/atoms/UserListItem.vue"
import CommentHeader from "src/components/atoms/CommentHeader.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import type { QTableBodyCellScope } from "src/components/tables/QueryTable.vue"
import {
  GetAvatarShowcaseUsersDocument,
  type GetAvatarShowcaseUsersQuery,
  type User as UserType,
  type Comment as CommentType
} from "src/graphql/generated/graphql"

type ShowcaseUser = GetAvatarShowcaseUsersQuery["users"]["data"][number]

const { result, loading } = useQuery(GetAvatarShowcaseUsersDocument)
const { currentUser } = useCurrentUser()

/**
 * Only show users that have an uploaded avatar *and* aren't you — both are
 * required for the ReportableAvatar overlay to render, and the whole point
 * of the showcase is to see the overlay.
 */
const users = computed<ShowcaseUser[]>(() => {
  const meId = currentUser.value?.id ? String(currentUser.value.id) : null
  return (result.value?.users.data ?? [])
    .filter((u) => !!u.avatar?.url && String(u.id) !== meId)
    .slice(0, 8)
})

function fakeScope(user: ShowcaseUser): QTableBodyCellScope {
  return {
    row: user,
    col: { name: "name", hideUsername: false },
    value: user,
    dense: true
  } as unknown as QTableBodyCellScope
}

function fakeComment(user: ShowcaseUser) {
  const now = new Date().toISOString()
  return {
    __typename: "OverallComment" as const,
    id: `showcase-${user.id}`,
    content: "Demo comment body.",
    created_at: now,
    updated_at: now,
    deleted_at: null,
    read_at: now,
    created_by: user,
    updated_by: user
  }
}
</script>
