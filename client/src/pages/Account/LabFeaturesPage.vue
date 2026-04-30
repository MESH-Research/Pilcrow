<template>
  <h1 class="text-h2 q-pl-md" data-cy="page_heading">
    {{ $t("lab_features.page_title") }}
  </h1>
  <div class="q-pa-md">
    <p class="text-body1 q-mb-md">
      {{ $t("lab_features.intro") }}
    </p>
    <q-card flat bordered>
      <q-card-section class="column q-gutter-y-lg">
        <div
          v-for="feature in labFeatures"
          :key="feature.key"
          class="lab-feature-row"
        >
          <h2 class="text-h3 q-my-none">{{ $t(feature.label) }}</h2>

          <div
            v-if="feature.previews && feature.previews.length > 0"
            class="lab-feature-previews q-mt-md"
            :data-cy="`lab_feature_${feature.key}_previews`"
          >
            <div class="row q-col-gutter-md">
              <div
                v-for="preview in feature.previews"
                :key="preview.key"
                class="col-12 col-sm-6 col-md-4"
              >
                <q-card
                  flat
                  bordered
                  class="lab-feature-preview-card cursor-pointer full-height column"
                  tabindex="0"
                  role="button"
                  :aria-label="
                    $t(`${feature.previewsKey}.expand_aria`, {
                      caption: $t(
                        `${feature.previewsKey}.${preview.key}.title`
                      )
                    })
                  "
                  :data-cy="`lab_preview_${preview.key}`"
                  @click="openPreview(feature.previewsKey, preview)"
                  @keydown.enter.prevent="
                    openPreview(feature.previewsKey, preview)
                  "
                  @keydown.space.prevent="
                    openPreview(feature.previewsKey, preview)
                  "
                >
                  <q-img
                    :src="previewSrc(preview)"
                    :ratio="16 / 10"
                    fit="cover"
                    position="top"
                    class="lab-feature-preview-thumb"
                    no-spinner
                  >
                    <div
                      class="absolute-bottom-right q-pa-xs lab-feature-preview-badge"
                    >
                      <q-icon name="zoom_in" size="sm" />
                    </div>
                  </q-img>
                  <q-card-section class="q-pa-sm col">
                    <div class="text-body2 text-weight-medium">
                      {{
                        $t(`${feature.previewsKey}.${preview.key}.title`)
                      }}
                    </div>
                    <div class="text-caption text-grey-7 q-mt-xs">
                      {{
                        $t(`${feature.previewsKey}.${preview.key}.caption`)
                      }}
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </div>

          <div class="row items-start q-gutter-x-md q-mt-md">
            <div class="col">
              <div class="text-body1 text-grey-8">
                {{ $t(feature.description) }}
              </div>
            </div>

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
        </div>
      </q-card-section>
    </q-card>

    <!-- Lightbox: centered dialog (default q-dialog sizing — not
         maximized) so the screenshot reads as a focused asset rather
         than a fullscreen takeover. The image sits on top, with the
         heading-sized title and caption below for natural top-down
         reading. The image area is height-capped so very tall screenshots
         scroll within the dialog instead of pushing the title off-screen. -->
    <q-dialog
      v-model="lightboxOpen"
      transition-show="fade"
      transition-hide="fade"
      data-cy="lab_preview_lightbox"
    >
      <q-card class="lab-feature-lightbox column">
        <q-card-section class="lab-feature-lightbox-body q-pa-none">
          <!-- Plain <img> rather than q-img: q-img collapses to zero
               height without a known aspect ratio, and the three
               screenshots all have different ratios. A native img
               sizes naturally to its content with the CSS caps below
               keeping it inside the dialog. -->
          <img
            v-if="activePreview"
            :src="previewSrc(activePreview)"
            :alt="activePreviewTitle"
            class="lab-feature-lightbox-img"
          />
        </q-card-section>
        <q-separator />
        <q-card-section class="row items-start q-gutter-x-md">
          <div class="col">
            <div class="text-h3 q-mb-xs">{{ activePreviewTitle }}</div>
            <div class="text-body2 text-grey-7">
              {{ activePreviewCaption }}
            </div>
          </div>
          <q-btn
            v-close-popup
            flat
            round
            dense
            icon="close"
            :aria-label="$t(`lab_features.manage_ui_v2.previews.close`)"
            data-cy="lab_preview_close"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
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
import { computed, ref } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"
import { useUserPreferences } from "src/use/userPreferences"
import { useFeedbackMessages } from "src/use/guiElements"
import { SetFeatureOptInDocument } from "src/graphql/generated/graphql"

interface LabPreview {
  key: string
  // Per-theme image so the previews track the user's current Quasar
  // dark/light mode rather than always shipping a screenshot taken
  // in the wrong theme.
  src: { light: string; dark: string }
}
interface LabFeatureDef {
  key: string
  label: string
  description: string
  // Base i18n key for previews (e.g. "lab_features.manage_ui_v2.previews").
  // Each preview's title/caption lives at `${previewsKey}.${preview.key}.title`
  // / `.caption`. Centralizing the prefix keeps the template readable.
  previewsKey?: string
  previews?: readonly LabPreview[]
}

const { optedInFeatures } = useUserPreferences()
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages()
const $q = useQuasar()

function previewSrc(preview: LabPreview): string {
  return $q.dark.isActive ? preview.src.dark : preview.src.light
}

// Known lab features. Hardcoded for now — when more land we can
// drive this from a server-supplied catalog. Each entry's `key`
// matches the value passed to `setFeatureOptIn(feature: ...)`;
// those identifiers stay technical (e.g. `manage_ui_v2`) since
// they're persisted server-side and shouldn't shift with the
// user-visible naming of this page.
//
// Preview screenshots live under `public/lab-features/` so their
// URLs are stable across builds without needing imports.
const labFeatures: readonly LabFeatureDef[] = [
  {
    key: "manage_ui_v2",
    label: "lab_features.manage_ui_v2.label",
    description: "lab_features.manage_ui_v2.description",
    previewsKey: "lab_features.manage_ui_v2.previews",
    previews: [
      {
        key: "manage_overview",
        src: {
          light: "/lab-features/manage-overview-light.png",
          dark: "/lab-features/manage-overview.png"
        }
      },
      {
        key: "publication_dashboard",
        src: {
          light: "/lab-features/publication-dashboard-light.png",
          dark: "/lab-features/publication-dashboard.png"
        }
      },
      {
        key: "submission_detail",
        src: {
          light: "/lab-features/submission-detail-light.png",
          dark: "/lab-features/submission-detail.png"
        }
      }
    ]
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

const lightboxOpen = ref(false)
const activePreview = ref<LabPreview | null>(null)
const activePreviewsKey = ref<string | null>(null)

function openPreview(previewsKey: string, preview: LabPreview) {
  activePreview.value = preview
  activePreviewsKey.value = previewsKey
  lightboxOpen.value = true
}

const activePreviewTitle = computed(() =>
  activePreview.value && activePreviewsKey.value
    ? t(`${activePreviewsKey.value}.${activePreview.value.key}.title`)
    : ""
)
const activePreviewCaption = computed(() =>
  activePreview.value && activePreviewsKey.value
    ? t(`${activePreviewsKey.value}.${activePreview.value.key}.caption`)
    : ""
)
</script>

<style scoped>
.lab-feature-row + .lab-feature-row {
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  padding-top: 24px;
}
.body--dark .lab-feature-row + .lab-feature-row {
  border-top-color: rgba(255, 255, 255, 0.12);
}
.lab-feature-preview-card {
  transition:
    box-shadow 120ms ease,
    border-color 120ms ease,
    transform 120ms ease;
}
.lab-feature-preview-card:hover,
.lab-feature-preview-card:focus-visible {
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
  border-color: rgba(0, 0, 0, 0.25);
  transform: translateY(-1px);
  outline: none;
}
.body--dark .lab-feature-preview-card:hover,
.body--dark .lab-feature-preview-card:focus-visible {
  box-shadow: 0 4px 14px rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.32);
}
.lab-feature-preview-thumb {
  background-color: rgba(0, 0, 0, 0.04);
}
.body--dark .lab-feature-preview-thumb {
  background-color: rgba(255, 255, 255, 0.04);
}

.lab-feature-preview-badge {
  background: rgba(0, 0, 0, 0.55);
  color: white;
  border-radius: 4px;
  line-height: 0;
}

.lab-feature-lightbox {
  width: min(960px, 95vw);
  max-width: 95vw;
}
.lab-feature-lightbox-body {
  /* Cap the image area at most ~70% of the viewport height so very
     tall screenshots don't push the caption off-screen on small
     displays. The image scales with `fit="contain"` so it never
     crops. */
  max-height: 70vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: rgba(0, 0, 0, 0.04);
}
.body--dark .lab-feature-lightbox-body {
  background-color: rgba(255, 255, 255, 0.04);
}
.lab-feature-lightbox-img {
  display: block;
  max-width: 100%;
  max-height: 70vh;
  height: auto;
  width: auto;
  object-fit: contain;
}
</style>
