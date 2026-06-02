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
  </labs-feature-panel>
</template>

<script setup lang="ts">
import LabsFeaturePanel from "src/components/labs/LabsFeaturePanel.vue"
import LabsFeaturePreviews, {
  type LabsPreview
} from "src/components/labs/LabsFeaturePreviews.vue"

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
