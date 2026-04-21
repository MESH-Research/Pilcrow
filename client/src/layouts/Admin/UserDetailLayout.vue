<template>
  <div v-if="!user" class="q-px-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-px-lg">
    <nav class="q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          label="Administration"
          :to="{ name: 'admin:dashboard' }"
        />
        <q-breadcrumbs-el :label="$t('user.self', 2)" to="/admin/users" />
        <q-breadcrumbs-el :label="$t('user.details_heading')" />
      </q-breadcrumbs>
    </nav>
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
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              :icon="user.avatar_upload_blocked ? 'block' : 'image'"
              :label="$t('admin.users.details.avatar_upload')"
            >
              <div class="row items-center q-gutter-sm">
                <span
                  :class="
                    user.avatar_upload_blocked ? 'text-negative' : 'text-grey-7'
                  "
                >
                  {{
                    user.avatar_upload_blocked
                      ? $t("admin.users.details.avatar_upload_blocked")
                      : $t("admin.users.details.avatar_upload_allowed")
                  }}
                </span>
                <q-btn
                  v-if="canModerateAvatars"
                  dense
                  size="sm"
                  :color="user.avatar_upload_blocked ? 'primary' : 'negative'"
                  :loading="togglingBlock"
                  :label="
                    user.avatar_upload_blocked
                      ? $t('admin.users.details.avatar_reinstate')
                      : $t('admin.users.details.avatar_revoke')
                  "
                  data-cy="avatar_upload_toggle_block"
                  @click="toggleBlock"
                />
              </div>
            </FieldDisplay>
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
          :to="{ name: 'user_details', params: { id: props.id } }"
          exact
          label="Publications"
        />
        <q-route-tab
          :to="{ name: 'user_details:submissions', params: { id: props.id } }"
          label="Submissions"
        />
      </q-tabs>
      <q-separator />

      <router-view :id="props.id" :user="user" />
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
      avatar_upload_blocked
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
import { useQuery, useMutation } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { DateTime } from "luxon"
import { Notify } from "quasar"
import { useI18n } from "vue-i18n"
import { useCurrentUser } from "src/use/user"
import { SET_USER_AVATAR_UPLOAD_BLOCKED } from "src/graphql/mutations"

interface Props {
  id: string
}
const props = defineProps<Props>()

const { t } = useI18n()
const { can } = useCurrentUser()
const canModerateAvatars = computed(() => can("moderate avatars"))

const { result, refetch } = useQuery(getUserDetailDocument, { id: props.id })
const user = computed(() => {
  return result.value?.user
})

const isAdmin = computed(() =>
  user.value?.roles.some((r) => r.name === "Application Administrator")
)

const { mutate: setBlocked } = useMutation(SET_USER_AVATAR_UPLOAD_BLOCKED)
const togglingBlock = ref(false)

async function toggleBlock() {
  if (!user.value) return
  togglingBlock.value = true
  try {
    await setBlocked({
      userId: user.value.id,
      blocked: !user.value.avatar_upload_blocked
    })
    Notify.create({
      type: "positive",
      message: user.value.avatar_upload_blocked
        ? t("admin.users.details.avatar_reinstated")
        : t("admin.users.details.avatar_revoked")
    })
    await refetch()
  } catch {
    Notify.create({
      type: "negative",
      message: t("admin.users.details.avatar_toggle_failure")
    })
  } finally {
    togglingBlock.value = false
  }
}

function formatDateTime(iso: string): string {
  return DateTime.fromISO(iso).toFormat("LLL d yyyy h:mm a")
}
</script>
