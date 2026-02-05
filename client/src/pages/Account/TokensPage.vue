<template>
  <div>
    <h1 class="text-h2 q-pl-md" data-cy="page_heading">
      {{ $t("tokens.page_title") }}
    </h1>
    <q-card-section>
      <p class="text-body1 q-mb-md">
        {{ $t("tokens.description") }}
      </p>

      <q-btn
        color="primary"
        :label="$t('tokens.create_button')"
        icon="add"
        data-cy="create_token_button"
        @click="openCreateDialog"
      />
    </q-card-section>

    <q-card-section>
      <q-list v-if="tokens?.length" bordered separator>
        <q-item v-for="token in tokens" :key="token.id" data-cy="token_item">
          <q-item-section avatar>
            <q-icon name="key" color="primary" />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ token.name }}</q-item-label>
            <q-item-label caption>
              {{ $t("tokens.created_at") }}:
              {{ formatDate(token.created_at) }}
            </q-item-label>
            <q-item-label v-if="token.last_used_at" caption>
              {{ $t("tokens.last_used") }}:
              {{ formatDate(token.last_used_at) }}
            </q-item-label>
            <q-item-label v-else caption class="text-grey">
              {{ $t("tokens.never_used") }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn
              flat
              round
              icon="delete"
              color="negative"
              :aria-label="$t('tokens.revoke_button')"
              data-cy="revoke_token_button"
              @click="confirmRevoke(token)"
            />
          </q-item-section>
        </q-item>
      </q-list>

      <div v-else-if="loading" class="text-center q-pa-md">
        <q-spinner size="md" />
      </div>

      <div v-else class="text-center text-grey q-pa-md" data-cy="no_tokens">
        {{ $t("tokens.no_tokens") }}
      </div>
    </q-card-section>
  </div>
</template>

<script setup>
import { computed } from "vue"
import { useQuery, useMutation } from "@vue/apollo-composable"
import { useQuasar, date } from "quasar"
import { useI18n } from "vue-i18n"
import { GET_PERSONAL_ACCESS_TOKENS } from "src/graphql/queries"
import {
  CREATE_PERSONAL_ACCESS_TOKEN,
  REVOKE_PERSONAL_ACCESS_TOKEN
} from "src/graphql/mutations"
import CreateTokenDialog from "src/components/dialogs/CreateTokenDialog.vue"

const $q = useQuasar()
const { t } = useI18n()

const { result, loading, refetch } = useQuery(GET_PERSONAL_ACCESS_TOKENS)
const tokens = computed(() => result.value?.personalAccessTokens ?? [])

const { mutate: createToken } = useMutation(CREATE_PERSONAL_ACCESS_TOKEN)
const { mutate: revokeToken } = useMutation(REVOKE_PERSONAL_ACCESS_TOKEN)

function formatDate(dateString) {
  if (!dateString) return ""
  return date.formatDate(new Date(dateString), "YYYY-MM-DD HH:mm")
}

function openCreateDialog() {
  $q.dialog({
    component: CreateTokenDialog
  }).onOk(async (tokenName) => {
    try {
      const result = await createToken({ name: tokenName })
      const plainToken = result.data.createPersonalAccessToken.token

      // Show the token to the user (only visible once)
      $q.dialog({
        title: t("tokens.token_created_title"),
        message: t("tokens.token_created_message"),
        html: true,
        prompt: {
          model: plainToken,
          type: "textarea",
          readonly: true,
          outlined: true,
          autogrow: true
        },
        ok: {
          label: t("tokens.token_copied_button"),
          flat: true
        },
        persistent: true
      }).onOk(() => {
        navigator.clipboard.writeText(plainToken)
        $q.notify({
          type: "positive",
          message: t("tokens.token_copied_notify")
        })
      })

      refetch()
    } catch (error) {
      $q.notify({
        type: "negative",
        message: t("tokens.create_error")
      })
    }
  })
}

function confirmRevoke(token) {
  $q.dialog({
    title: t("tokens.revoke_confirm_title"),
    message: t("tokens.revoke_confirm_message", { name: token.name }),
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await revokeToken({ id: token.id })
      $q.notify({
        type: "positive",
        message: t("tokens.revoke_success")
      })
      refetch()
    } catch (error) {
      $q.notify({
        type: "negative",
        message: t("tokens.revoke_error")
      })
    }
  })
}
</script>
