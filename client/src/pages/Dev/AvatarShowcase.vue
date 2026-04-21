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
        <h2 class="text-h6">NameAvatarCell (in-table replica)</h2>
        <p class="text-caption text-grey-7">
          Used in admin QueryTable rows (e.g. /admin/users). We copy the
          markup rather than mount the real component because QTd relies
          on internal <code>col.__tdClass</code> / scope wiring that
          only QTable provides.
        </p>
        <q-markup-table flat bordered dense>
          <thead>
            <tr>
              <th class="text-left">Name</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="'nac-' + user.id">
              <td class="text-left">
                <q-item class="q-pa-none">
                  <q-item-section side>
                    <avatar-image :user="user" size="40px" rounded />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label v-if="user.name">
                      {{ user.name }}
                    </q-item-label>
                    <q-item-label v-if="user.username" caption>
                      {{ user.username }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">Full comment (in-situ replica)</h2>
        <p class="text-caption text-grey-7">
          Hand-rolled replica of the OverallComment card + CommentHeader
          layout. We don't use the real CommentHeader component here
          because its CommentActions child depends on submission context
          (an injected <code>comment</code> and
          <code>useSubmission()</code>) that doesn't exist outside a
          submission page.
        </p>
        <q-card
          v-for="user in users"
          :key="'sit-' + user.id"
          square
          class="bg-grey-1 shadow-2 q-mb-md comment"
        >
          <q-card-section class="q-py-xs q-pl-xs">
            <div class="row items-center">
              <q-btn flat dense class="q-mr-xs">
                <q-icon size="xs" name="chat_bubble" color="primary" />
              </q-btn>
              <reportable-avatar
                :user="user"
                compact
                round
                size="30px"
                class="q-mr-sm"
              />
              <div class="text-h4 ellipsis">
                {{ user.display_label }}
              </div>
              <q-space />
              <div class="text-caption text-no-wrap text-grey-7">
                just now
              </div>
              <q-btn
                flat
                dense
                round
                icon="more_vert"
                color="dark-grey"
                class="q-ml-sm"
              />
            </div>
          </q-card-section>
          <q-card-section>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit.
            Demonstrative comment body so the author identity reads in the
            same visual context as a real review comment.
          </q-card-section>
        </q-card>
      </section>

      <section class="q-mb-xl">
        <h2 class="text-h6">Avatar + username combinations (raw markup)</h2>
        <p class="text-caption text-grey-7">
          Not using any identity component — just the avatar next to the
          display label at a few common sizes so you can eyeball
          baseline alignment.
        </p>
        <q-card flat bordered class="q-pa-md">
          <div
            v-for="user in users"
            :key="'combo-' + user.id"
            class="q-mb-md"
          >
            <div class="row items-center q-gutter-md q-mb-sm">
              <avatar-image :user="user" round size="24px" />
              <div>
                <span class="text-body2 text-weight-bold">
                  {{ user.display_label }}
                </span>
                <span class="text-caption text-grey-7 q-ml-xs">
                  @{{ user.username }}
                </span>
              </div>
            </div>
            <div class="row items-center q-gutter-md q-mb-sm">
              <avatar-image :user="user" round size="30px" />
              <div>
                <div class="text-body1 text-weight-bold">
                  {{ user.display_label }}
                </div>
                <div class="text-caption text-grey-7">
                  @{{ user.username }}
                </div>
              </div>
            </div>
            <div class="row items-center q-gutter-md">
              <avatar-image :user="user" rounded size="48px" />
              <div>
                <div class="text-subtitle1 text-weight-bold">
                  {{ user.display_label }}
                </div>
                <div class="text-body2 text-grey-7">
                  @{{ user.username }}
                </div>
              </div>
            </div>
            <q-separator class="q-mt-md" />
          </div>
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
import {
  GetAvatarShowcaseUsersDocument,
  type GetAvatarShowcaseUsersQuery,
  type User as UserType
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

</script>
