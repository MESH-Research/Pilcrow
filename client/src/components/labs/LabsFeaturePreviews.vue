<template>
  <div class="labs-feature-previews" :data-cy="`labs_previews_${previewsKey}`">
    <div class="row q-col-gutter-md">
      <div
        v-for="preview in previews"
        :key="preview.key"
        class="col-12 col-sm-6 col-md-4"
      >
        <q-card
          flat
          bordered
          class="labs-feature-preview-card cursor-pointer full-height column"
          tabindex="0"
          role="button"
          :aria-label="
            $t('labs.previews.expand_aria', {
              caption: $t(`${previewsKey}.${preview.key}.title`)
            })
          "
          :data-cy="`labs_preview_${preview.key}`"
          @click="openPreview(preview)"
          @keydown.enter.prevent="openPreview(preview)"
          @keydown.space.prevent="openPreview(preview)"
        >
          <q-img
            :src="resolveSrc(preview)"
            :ratio="16 / 10"
            fit="cover"
            position="top"
            class="labs-feature-preview-thumb"
            no-spinner
          >
            <div
              class="absolute-bottom-right q-pa-xs labs-feature-preview-badge"
            >
              <q-icon name="zoom_in" size="sm" />
            </div>
          </q-img>
          <q-card-section class="q-pa-sm col">
            <div class="text-body2 text-weight-medium">
              {{ $t(`${previewsKey}.${preview.key}.title`) }}
            </div>
            <div class="text-caption labs-feature-caption q-mt-xs">
              {{ $t(`${previewsKey}.${preview.key}.caption`) }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Lightbox: centered dialog (default q-dialog sizing, not maximized)
         so the screenshot reads as a focused asset. Image on top, title +
         caption below for natural top-down reading. The image area is
         height-capped so tall screenshots scroll within the dialog instead
         of pushing the title off-screen. -->
    <q-dialog
      v-model="lightboxOpen"
      transition-show="fade"
      transition-hide="fade"
      data-cy="labs_preview_lightbox"
    >
      <q-card class="labs-feature-lightbox column">
        <q-card-section class="labs-feature-lightbox-body q-pa-none">
          <!-- Plain <img> rather than q-img: previews have varying aspect
               ratios, and q-img collapses without a known ratio. A native
               img sizes to its content with the CSS caps below keeping it
               inside the dialog. -->
          <img
            v-if="activePreview"
            :src="resolveSrc(activePreview)"
            :alt="activeTitle"
            class="labs-feature-lightbox-img"
          />
        </q-card-section>
        <q-separator />
        <q-card-section class="row items-start q-gutter-x-md">
          <div class="col">
            <div class="text-h3 q-mb-xs">{{ activeTitle }}</div>
            <div class="text-body2 labs-feature-caption">
              {{ activeCaption }}
            </div>
          </div>
          <q-btn
            v-close-popup
            flat
            round
            dense
            icon="close"
            :aria-label="$t('labs.previews.close')"
            data-cy="labs_preview_close"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"

export interface LabsPreview {
  // Identifier; title/caption resolve from `${previewsKey}.${key}.{title,caption}`.
  key: string
  // Public asset URL for the light-mode screenshot (e.g.
  // "/lab-features/record-of-review.png"). Also the fallback when no dark
  // variant exists.
  src: string
  // Optional dark-mode screenshot, shown when the app is in dark mode.
  // Omit for previews that look the same in both themes.
  srcDark?: string
}

const props = defineProps<{
  previews: readonly LabsPreview[]
  // i18n base key; each preview's title/caption live under it.
  previewsKey: string
}>()

const { t } = useI18n()
const $q = useQuasar()

const lightboxOpen = ref(false)
const activePreview = ref<LabsPreview | null>(null)

// Pick the dark screenshot in dark mode when one exists; otherwise fall
// back to the light asset. Reactive on `$q.dark.isActive`, so the thumbs
// and lightbox swap as the theme toggles.
function resolveSrc(preview: LabsPreview) {
  return $q.dark.isActive && preview.srcDark ? preview.srcDark : preview.src
}

function openPreview(preview: LabsPreview) {
  activePreview.value = preview
  lightboxOpen.value = true
}

const activeTitle = computed(() =>
  activePreview.value
    ? t(`${props.previewsKey}.${activePreview.value.key}.title`)
    : ""
)
const activeCaption = computed(() =>
  activePreview.value
    ? t(`${props.previewsKey}.${activePreview.value.key}.caption`)
    : ""
)
</script>

<style scoped>
.labs-feature-preview-card {
  transition:
    box-shadow 120ms ease,
    border-color 120ms ease,
    transform 120ms ease;
}
/* Caption grey reads fine on light, but grey-7 is too dark on the dark
   surface — lift to grey-5 for legible contrast in dark mode. */
.labs-feature-caption {
  color: #616161; /* $grey-7 */
}
.body--dark .labs-feature-caption {
  color: #9e9e9e; /* $grey-5 */
}
.labs-feature-preview-card:hover,
.labs-feature-preview-card:focus-visible {
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
  border-color: rgba(0, 0, 0, 0.25);
  transform: translateY(-1px);
  outline: none;
}
.body--dark .labs-feature-preview-card:hover,
.body--dark .labs-feature-preview-card:focus-visible {
  box-shadow: 0 4px 14px rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.32);
}
.labs-feature-preview-thumb {
  background-color: rgba(0, 0, 0, 0.04);
}
.body--dark .labs-feature-preview-thumb {
  background-color: rgba(255, 255, 255, 0.04);
}
.labs-feature-preview-badge {
  background: rgba(0, 0, 0, 0.55);
  color: white;
  border-radius: 4px;
  line-height: 0;
}
.labs-feature-lightbox {
  width: min(960px, 95vw);
  max-width: 95vw;
}
.labs-feature-lightbox-body {
  /* Cap the image area at ~70% of viewport height so tall screenshots
     don't push the caption off-screen; the image scales to fit. */
  max-height: 70vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: rgba(0, 0, 0, 0.04);
}
.body--dark .labs-feature-lightbox-body {
  background-color: rgba(255, 255, 255, 0.04);
}
.labs-feature-lightbox-img {
  display: block;
  max-width: 100%;
  max-height: 70vh;
  height: auto;
  width: auto;
  object-fit: contain;
}
</style>
