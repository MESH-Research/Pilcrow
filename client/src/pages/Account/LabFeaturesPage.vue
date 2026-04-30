<template>
  <h1 class="text-h2 q-pl-md" data-cy="page_heading">
    {{ $t("lab_features.page_title") }}
  </h1>
  <div class="q-pa-md">
    <p class="text-body1 q-mb-md">
      {{ $t("lab_features.intro") }}
    </p>
    <q-card flat bordered>
      <q-card-section class="column q-gutter-y-md">
        <div
          v-for="feature in labFeatures"
          :key="feature.key"
          class="row items-start q-gutter-x-md lab-feature-row"
        >
          <div class="col">
            <div class="text-body1">{{ $t(feature.label) }}</div>
            <div class="text-caption text-grey-7">
              {{ $t(feature.description) }}
            </div>
          </div>
          <!-- Explicit activate / deactivate button rather than a
               toggle so the action stays a deliberate click instead
               of a stray flip while scanning the list. The label
               flips to "Deactivate" once the feature is on, and the
               color shifts to a quieter outline so the active state
               doesn't shout for attention from a calm settings page. -->
          <q-btn
            no-caps
            :outline="optedInFeatures.includes(feature.key)"
            :color="
              optedInFeatures.includes(feature.key) ? 'grey-8' : 'primary'
            "
            :label="
              optedInFeatures.includes(feature.key)
                ? $t('lab_features.deactivate')
                : $t('lab_features.activate')
            "
            :loading="savingFeatureKey === feature.key"
            :data-cy="`lab_feature_${feature.key}`"
            @click="
              onLabToggle(feature.key, !optedInFeatures.includes(feature.key))
            "
          />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Co-located: lab-feature opt-ins are toggled here and nowhere
// else in the app, so the mutation lives with its UI rather than
// in the global mutations file.
graphql(`
  mutation SetFeatureOptIn($feature: String!, $enabled: Boolean!) {
    setFeatureOptIn(feature: $feature, enabled: $enabled) {
      id
      feature_opt_ins
    }
  }
`)
</script>

<script setup lang="ts">
import { ref } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useUserPreferences } from "src/use/userPreferences"
import { useFeedbackMessages } from "src/use/guiElements"
import { SetFeatureOptInDocument } from "src/graphql/generated/graphql"

const { optedInFeatures } = useUserPreferences()
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages()

// Known lab features. Hardcoded for now — when more land we can
// drive this from a server-supplied catalog. Each entry's `key`
// matches the value passed to `setFeatureOptIn(feature: ...)`;
// those identifiers stay technical (e.g. `manage_ui_v2`) since
// they're persisted server-side and shouldn't shift with the
// user-visible naming of this page.
const labFeatures = [
  {
    key: "manage_ui_v2",
    label: "lab_features.manage_ui_v2.label",
    description: "lab_features.manage_ui_v2.description"
  }
] as const

const { mutate: setFeatureOptInMutation } = useMutation(SetFeatureOptInDocument)
// Track which feature is currently saving so only that toggle is
// disabled — leaving the others usable for parallel edits.
const savingFeatureKey = ref<string | null>(null)

async function onLabToggle(feature: string, enabled: boolean) {
  savingFeatureKey.value = feature
  try {
    await setFeatureOptInMutation({ feature, enabled })
  } catch {
    newStatusMessage("failure", t("lab_features.error"))
  } finally {
    savingFeatureKey.value = null
  }
}
</script>

<style scoped>
.lab-feature-row + .lab-feature-row {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  padding-top: 12px;
}
.body--dark .lab-feature-row + .lab-feature-row {
  border-top-color: rgba(255, 255, 255, 0.12);
}
</style>
