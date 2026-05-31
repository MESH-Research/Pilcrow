<template>
  <labs-feature-panel feature-key="labs_test" label="labs.labs_test.label">
    {{ $t("labs.labs_test.description") }}
  </labs-feature-panel>
</template>

<script setup lang="ts">
import LabsFeaturePanel from "src/components/labs/LabsFeaturePanel.vue"

definePage({
  name: "account:labs:labs-test",
  meta: {
    feature: { key: "labs_test", private: true, order: 10 }
  }
})
</script>
