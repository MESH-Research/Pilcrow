<template>
  <div v-if="!user" class="q-px-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-px-lg">
    <nav class="q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el :label="$t('user.self', 2)" to="/admin/users" />
        <q-breadcrumbs-el :label="$t('user.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <div class="row">
      <h2>{{ $t("admin.users.details.title") }}</h2>
    </div>
    <div class="column">
      <avatar-image :user="user" rounded style="width: 100px; height: 100px" />
      <div class="column q-gutter-md">
        <ItemCaptioned
          icon="person"
          :value="user.username"
          t-caption="username"
          t-prefix="admin.users.details"
        />
        <ItemCaptioned
          :value="user.name ?? ''"
          t-caption="name"
          t-prefix="admin.users.details"
        />
        <ItemCaptioned
          :value="user.email"
          t-caption="email"
          icon="email"
          t-prefix="admin.users.details"
        />
        <ItemCaptioned
          t-caption="role"
          icon="key"
          t-prefix="admin.users.details"
        >
          <template #value="{ pt }">
            <span v-if="isAdmin">
              {{ pt("isAdmin") }}
            </span>
            <span v-else>{{ pt("isNormal") }}</span>
          </template>
        </ItemCaptioned>
      </div>

      <h3>Publications</h3>

      <div class="column">
        <q-list bordered separator>
          <q-item
            v-for="publication in user.publications"
            :key="publication.id"
            flat
            bordered
            clickable
            :to="{
              name: 'publication_details',
              params: { id: publication.id }
            }"
          >
            <q-item-section>
              <q-item-label>
                {{ publication.name }}
              </q-item-label>
              <q-item-label>
                <q-chip size="sm">{{
                  $t(`admin.users.details.roles.${publication.my_role}`)
                }}</q-chip>
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>

      <h3>Submissions</h3>
      <UserDetailsSubmissions :id="props.id" />
    </div>
  </article>
</template>

<script setup lang="ts">
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import ItemCaptioned from "src/components/molecules/ItemCaptioned.vue"
import UserDetailsSubmissions from "src/pages/Admin/UserDetailsSubmissions.vue"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import gql from "graphql-tag"

interface Props {
  id: string
}
const props = defineProps<Props>()

interface UserRole {
  name: string
}

interface UserPublication {
  id: string
  name: string
  my_role: string
}

interface UserResult {
  user: {
    username: string
    email: string
    name: string | null
    roles: UserRole[]
    publications: UserPublication[]
  }
}

const GET_USER_DETAIL = gql`
  query getUserDetail($id: ID) {
    user(id: $id) {
      username
      email
      name
      roles {
        name
      }
      publications {
        id
        name
        my_role
      }
    }
  }
`

const { result } = useQuery<UserResult>(GET_USER_DETAIL, { id: props.id })
const user = computed(() => {
  return result.value?.user
})

const isAdmin = computed(() =>
  user.value?.roles.some((r) => r.name === "Application Administrator")
)
</script>
