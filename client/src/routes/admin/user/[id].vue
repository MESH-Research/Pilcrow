<template>
  <div v-if="!user" class="q-px-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-px-lg">
    <q-card flat bordered class="q-mt-md">
      <q-card-section horizontal>
        <q-card-section class="flex items-start q-pr-none">
          <avatar-image :user="user" rounded size="80px" />
        </q-card-section>
        <q-card-section class="col">
          <div class="text-h5">{{ user.name || user.username }}</div>
          <div class="text-subtitle1 text-grey-7 q-mb-sm">
            @{{ user.username }}
          </div>
          <div class="row q-col-gutter-x-lg q-col-gutter-y-sm">
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="email"
              :label="$t('admin.users.details.email')"
              :value="user.email"
            />
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="verified"
              :label="$t('admin.users.details.verified_at')"
            >
              <span v-if="user.email_verified_at">
                {{ formatDateTime(user.email_verified_at) }}
              </span>
              <span v-else class="text-grey-5">
                {{ $t("admin.users.details.not_verified") }}
              </span>
            </FieldDisplay>
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="key"
              :label="$t('admin.users.details.role')"
            >
              <span v-if="isAdmin">
                {{ $t("admin.users.details.isAdmin") }}
              </span>
              <span v-else>{{ $t("admin.users.details.isNormal") }}</span>
            </FieldDisplay>
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="calendar_today"
              :label="$t('admin.users.details.created_at')"
              :value="formatDateTime(user.created_at)"
            />
          </div>
        </q-card-section>
      </q-card-section>
    </q-card>

    <div class="column">
      <q-tabs
        align="left"
        class="q-mt-md"
        active-color="primary"
        indicator-color="primary"
      >
        <q-route-tab
          :to="{ name: 'user_details', params: { id } }"
          exact
          label="Publications"
        />
        <q-route-tab
          :to="{ name: 'user_details:submissions', params: { id } }"
          label="Submissions"
        />
        <q-route-tab
          :to="{ name: 'user_details:settings', params: { id } }"
          label="Settings"
        />
      </q-tabs>
      <q-separator />

      <router-view />
    </div>
  </article>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query getUserDetail($id: ID) {
    user(id: $id) {
      id
      username
      email
      name
      created_at
      email_verified_at
      ...avatarImage
      roles {
        name
      }
    }
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import FieldDisplay from "src/components/molecules/FieldDisplay.vue"
import { getUserDetailDocument } from "src/graphql/generated/graphql"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import { useRoute } from "vue-router"
import { DateTime } from "luxon"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  // Named so setCrumbLabel below can target it by name. The user
  // never navigates to this layout directly — child routes own the
  // URLs — but vue-router still lets layout routes carry names.
  name: "admin:user:id",
  // Stacks the ancestor "Users" crumb (linking back to admin:users)
  // in front of this route's dynamic user-name crumb in one go, so
  // we don't need a separate routes/admin/user.vue layout just to
  // hold the "Users" link.
  meta: {
    crumb: [{ label: "Users", to: { name: "admin:users" } }, { label: "User" }]
  }
})

const route = useRoute("admin:user:id")
const id = computed(() => route.params.id as string)

const { result } = useQuery(getUserDetailDocument, () => ({
  id: id.value
}))
const user = computed(() => result.value?.user)

const isAdmin = computed(() =>
  user.value?.roles.some((r) => r.name === "Application Administrator")
)

// Swap the placeholder "User" crumb label for the user's name once
// the query resolves. breadcrumbs applies the override to the last
// crumb this route contributes, which is the user-name slot above.
setCrumbLabel(
  "admin:user:id",
  computed(() => user.value?.name || user.value?.username || undefined)
)

function formatDateTime(iso: string): string {
  return DateTime.fromISO(iso).toFormat("LLL d yyyy h:mm a")
}
</script>
