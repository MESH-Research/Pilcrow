<template>
  <div v-if="loading" class="text-grey-7 q-pa-md">{{ $t("loading") }}</div>
  <div v-else-if="!user" class="text-grey-7 q-pa-md">
    {{ $t("admin.users.details.settings.not_found") }}
  </div>
  <article v-else class="q-pa-md column q-gutter-y-md">
    <q-card flat bordered>
      <q-card-section class="q-py-sm">
        <h3 class="section-heading">
          {{ $t("admin.users.details.settings.preferences.heading") }}
        </h3>
      </q-card-section>
      <q-separator />
      <q-card-section class="row q-col-gutter-x-lg q-col-gutter-y-sm">
        <FieldDisplay
          class="col-sm-6 col-xs-12"
          icon="palette"
          :label="$t('admin.users.details.settings.preferences.theme')"
          :value="themeLabel"
        />
        <FieldDisplay
          class="col-sm-6 col-xs-12"
          icon="accessibility_new"
          :label="
            $t('admin.users.details.settings.preferences.a11y_color_patterns')
          "
          :value="a11yColorPatternsLabel"
        />
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-card-section class="q-py-sm">
        <h3 class="section-heading">
          {{ $t("admin.users.details.settings.dismissed.heading") }}
          <span class="text-grey-7 text-body2">
            ({{ user.dismissed_ui.length }})
          </span>
        </h3>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <p v-if="user.dismissed_ui.length === 0" class="text-grey-7 q-my-none">
          {{ $t("admin.users.details.settings.dismissed.none") }}
        </p>
        <q-list v-else dense>
          <q-item
            v-for="key in user.dismissed_ui"
            :key="key"
            class="settings-key-row"
          >
            <q-item-section>
              <q-item-label class="settings-key">{{ key }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-card-section class="q-py-sm">
        <h3 class="section-heading">
          {{ $t("admin.users.details.settings.feature_opt_ins.heading") }}
          <span class="text-grey-7 text-body2">
            ({{ user.feature_opt_ins.length }})
          </span>
        </h3>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <p
          v-if="user.feature_opt_ins.length === 0"
          class="text-grey-7 q-my-none"
        >
          {{ $t("admin.users.details.settings.feature_opt_ins.none") }}
        </p>
        <q-list v-else dense>
          <q-item
            v-for="feature in user.feature_opt_ins"
            :key="feature"
            class="settings-key-row"
          >
            <q-item-section>
              <q-item-label class="settings-key">{{ feature }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>
  </article>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query getUserSettings($id: ID) {
    user(id: $id) {
      id
      preferences {
        theme
        a11y_color_patterns
      }
      dismissed_ui
      feature_opt_ins
    }
  }
`)
</script>

<script setup lang="ts">
import FieldDisplay from "src/components/molecules/FieldDisplay.vue"
import { getUserSettingsDocument } from "src/graphql/generated/graphql"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { useRoute } from "vue-router"

definePage({
  name: "user_details:settings",
  meta: {
    crumb: { label: "Settings" }
  }
})

const route = useRoute("user_details:settings")
const id = computed(() => route.params.id as string)

const { result, loading } = useQuery(getUserSettingsDocument, () => ({
  id: id.value
}))

const user = computed(() => result.value?.user)

const { t } = useI18n()

// Translate the stored theme enum back to a human-readable label.
// `null` (no preference set) reads as "Auto" since AUTO is the
// behavioral default the app falls back to.
const themeLabel = computed(() => {
  const theme = user.value?.preferences?.theme
  if (!theme) return t("admin.users.details.settings.preferences.theme_auto")
  return t(
    `admin.users.details.settings.preferences.theme_${theme.toLowerCase()}`
  )
})

const a11yColorPatternsLabel = computed(() => {
  const value = user.value?.preferences?.a11y_color_patterns
  if (value === null || value === undefined) {
    return t("admin.users.details.settings.preferences.not_set")
  }
  return value
    ? t("admin.users.details.settings.preferences.enabled")
    : t("admin.users.details.settings.preferences.disabled")
})
</script>

<style scoped>
.settings-key-row {
  border-radius: 4px;
  padding-left: 0;
}
.settings-key {
  font-family: var(--q-font-mono, "SFMono-Regular", Menlo, Consolas, monospace);
  font-size: 0.875rem;
}
</style>
