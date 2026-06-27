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
          <div
            :class="darkModeStatus ? 'text-grey-5' : 'text-grey-8'"
            class="text-subtitle1 q-mb-sm"
          >
            @{{ user.username }}
          </div>
          <div class="row q-col-gutter-x-lg q-col-gutter-y-sm">
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="email"
              :label="$t('admin.users.details.email')"
              :value="user.email"
              :dark-mode-status="darkModeStatus"
            />
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="verified"
              :label="$t('admin.users.details.verified_at')"
              :dark-mode-status="darkModeStatus"
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
              :dark-mode-status="darkModeStatus"
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
              :dark-mode-status="darkModeStatus"
            />
            <FieldDisplay
              class="col-sm-6 col-xs-12"
              icon="science"
              :label="$t('admin.users.details.beta')"
              :dark-mode-status="darkModeStatus"
            >
              <q-toggle
                :model-value="user.beta"
                :loading="savingBeta"
                :disable="savingBeta"
                :label="
                  user.beta
                    ? $t('admin.users.details.beta_enabled')
                    : $t('admin.users.details.beta_disabled')
                "
                data-cy="user_beta_toggle"
                @update:model-value="onBetaToggle"
              />
            </FieldDisplay>
            <FieldDisplay
              class="col-xs-12"
              icon="o_science"
              :label="$t('admin.users.details.feature_opt_ins')"
              :dark-mode-status="darkModeStatus"
            >
              <div
                v-if="optedInFeatures.length"
                class="row q-gutter-xs"
                data-cy="user_feature_opt_ins"
              >
                <q-chip
                  v-for="feature in optedInFeatures"
                  :key="feature.key"
                  square
                  color="primary"
                  text-color="white"
                  :label="feature.label"
                />
              </div>
              <span
                v-else
                :class="darkModeStatus ? 'text-grey-5' : 'text-grey-8'"
                data-cy="user_no_feature_opt_ins"
              >
                {{ $t("admin.users.details.no_feature_opt_ins") }}
              </span>
            </FieldDisplay>
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
      <q-tabs align="left" class="q-mt-md" indicator-color="primary">
        <q-route-tab
          :to="{ name: 'user_details', params: { id } }"
          exact
          :label="$t('admin.users.details.tabs.publications')"
        />
        <q-route-tab
          :to="{ name: 'user_details:submissions', params: { id } }"
          :label="$t('admin.users.details.tabs.submissions')"
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
      beta
      feature_opt_ins
      avatar_upload_blocked
      ...avatarImage
      roles {
        name
      }
    }
  }
`)

graphql(`
  mutation SetUserBetaAccess($id: ID!, $enabled: Boolean!) {
    setUserBetaAccess(id: $id, enabled: $enabled) {
      id
      beta
    }
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import FieldDisplay from "src/components/molecules/FieldDisplay.vue"
import {
  getUserDetailDocument,
  SetUserBetaAccessDocument
} from "src/graphql/generated/graphql"
import { SET_USER_AVATAR_UPLOAD_BLOCKED } from "src/graphql/mutations"
import { useMutation, useQuery } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { useRoute } from "vue-router"
import { DateTime } from "luxon"
import { useI18n } from "vue-i18n"
import { setCrumbLabel } from "src/use/breadcrumbs"
import { useFeedbackMessages, useDarkMode } from "src/use/guiElements"
import { useCurrentUser } from "src/use/user"

definePage({
  // Named so setCrumbLabel below can target it by name. The user
  // never navigates to this layout directly — child routes own the
  // URLs — but vue-router still lets layout routes carry names.
  name: "admin:user:id",
  // The "Users" ancestor crumb comes from the section layout
  // (../users.vue); this route only adds the dynamic user-name leaf.
  meta: {
    crumb: { label: "breadcrumbs.admin.user" }
  }
})

const { darkModeStatus } = useDarkMode()
const route = useRoute("admin:user:id")
const id = computed(() => route.params.id as string)

const { result, refetch } = useQuery(getUserDetailDocument, () => ({
  id: id.value
}))
const user = computed(() => result.value?.user)

const isAdmin = computed(() =>
  user.value?.roles.some((r) => r.name === "Application Administrator")
)

const { can } = useCurrentUser()
const canModerateAvatars = computed(() => can("admin_avatar_moderate"))

const { t, te } = useI18n()

// Map each opted-in feature key to its client-side Labs label, falling
// back to the raw key when the catalog has no label (e.g. a key that was
// retired from the client but is still stored on the user).
const optedInFeatures = computed(() =>
  (user.value?.feature_opt_ins ?? []).map((key) => {
    const labelKey = `labs.${key}.label`
    return { key, label: te(labelKey) ? t(labelKey) : key }
  })
)

const { newStatusMessage } = useFeedbackMessages()
const { mutate: setUserBetaAccess } = useMutation(SetUserBetaAccessDocument)
const savingBeta = ref(false)

async function onBetaToggle(enabled: boolean) {
  if (!user.value) return
  savingBeta.value = true
  try {
    await setUserBetaAccess({ id: user.value.id, enabled })
  } catch {
    newStatusMessage("failure", t("admin.users.details.beta_error"))
  } finally {
    savingBeta.value = false
  }
}

const { mutate: setBlocked } = useMutation(SET_USER_AVATAR_UPLOAD_BLOCKED)
const togglingBlock = ref(false)

async function toggleBlock() {
  if (!user.value) return
  const wasBlocked = user.value.avatar_upload_blocked
  togglingBlock.value = true
  try {
    await setBlocked({
      userId: user.value.id,
      blocked: !wasBlocked
    })
    newStatusMessage(
      "success",
      wasBlocked
        ? t("admin.users.details.avatar_reinstated")
        : t("admin.users.details.avatar_revoked")
    )
    await refetch()
  } catch {
    newStatusMessage("failure", t("admin.users.details.avatar_toggle_failure"))
  } finally {
    togglingBlock.value = false
  }
}

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
