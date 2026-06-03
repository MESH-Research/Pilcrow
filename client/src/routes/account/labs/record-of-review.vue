<template>
  <labs-feature-panel
    feature-key="record_of_review"
    label="labs.record_of_review.label"
  >
    <p class="q-mb-md">{{ $t("labs.record_of_review.description") }}</p>
    <labs-feature-previews
      :previews="previews"
      previews-key="labs.record_of_review.previews"
    />
    <div class="row items-center q-gutter-sm q-mt-lg">
      <span>{{ $t("labs.record_of_review.feedback_lead") }}</span>
      <q-btn
        class="ror-feedback-btn"
        type="a"
        :href="feedbackUrl"
        target="_blank"
        rel="noopener noreferrer"
        no-caps
        outline
        color="primary"
        icon="forum"
        icon-right="open_in_new"
        :label="$t('labs.record_of_review.feedback')"
        data-cy="ror_feedback_link"
      />
    </div>
  </labs-feature-panel>
</template>

<script setup lang="ts">
import LabsFeaturePanel from "src/components/labs/LabsFeaturePanel.vue"
import LabsFeaturePreviews, {
  type LabsPreview
} from "src/components/labs/LabsFeaturePreviews.vue"

// Deep-links to the Fider feedback board with the Record of Review tag
// pre-selected. Fider only supports prefilling `tags` via the URL — the
// title and description are left for the user to fill in.
const feedbackUrl = "https://feedback.pilcrow.dev/"

definePage({
  name: "account:labs:record-of-review",
  meta: {
    // `key` matches the server `features.beta` catalog. `private: true`
    // keeps the opt-in hidden from users without beta access; `order`
    // sorts the Labs list (gaps left for future entries).
    feature: { key: "record_of_review", private: true, order: 10 }
  }
})

// Preview screenshots live under public/lab-features/ so their URLs are
// stable across builds without imports. Titles/captions resolve from
// labs.record_of_review.previews.<key>.{title,caption}.
const previews: readonly LabsPreview[] = [
  { key: "record", src: "/lab-features/record-of-review.png" },
  {
    key: "list",
    src: "/lab-features/record-of-review-list-light.png",
    srcDark: "/lab-features/record-of-review-list-dark.png"
  }
]
</script>

<style lang="sass" scoped>
// Global `a[target="_blank"]::after` appends an open_in_new glyph, but inside
// the q-btn flex it lands outside the content row and wraps to a new line.
// Suppress it here and use a real `icon-right` that sits within the content.
.ror-feedback-btn::after
  content: none
</style>
