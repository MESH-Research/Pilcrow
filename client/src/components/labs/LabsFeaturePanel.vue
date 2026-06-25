<template>
  <q-card flat bordered>
    <q-card-section>
      <h2 class="text-h3 q-mt-none q-mb-sm">
        <!-- #title: override the heading entirely. -->
        <slot name="title">
          <template v-if="label">{{ $t(label) }}</template>
        </slot>
      </h2>

      <!-- Default slot: the feature's body. Each feature supplies its own
           description markup; receives the opt-in state so it can render
           richer, state-aware content. -->
      <div class="text-body1 labs-feature-body q-mb-lg">
        <slot :opted-in="optedIn" :saving="saving" :toggle="toggle" />
      </div>

      <q-btn
        no-caps
        :color="optedIn ? 'negative' : 'primary'"
        :label="optedIn ? $t('labs.deactivate') : $t('labs.activate')"
        :loading="saving"
        :data-cy="`labs_feature_${featureKey}`"
        @click="toggle"
      />
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Co-located registration of the opt-in mutation. This component is the
// only consumer of Labs opt-in toggling, so the document lives with it.
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
import { useLabsFeature } from "src/use/features"

const props = defineProps<{
  // Technical key, matched against the server `features.beta` catalog.
  featureKey: string
  // i18n key for the default heading. Optional — a feature can supply its
  // own heading via the #title slot instead.
  label?: string
}>()

defineSlots<{
  title?: () => unknown
  default?: (props: {
    optedIn: boolean
    saving: boolean
    toggle: () => Promise<void>
  }) => unknown
}>()

const { optedIn, saving, toggle } = useLabsFeature(props.featureKey)
</script>

<style scoped>
/* grey-8 body copy is fine on light but too dark to read on the dark
   surface — lift to grey-4 in dark mode for legible contrast. */
.labs-feature-body {
  color: #424242; /* $grey-8 */
}
.body--dark .labs-feature-body {
  color: #bdbdbd; /* $grey-4 */
}
</style>
