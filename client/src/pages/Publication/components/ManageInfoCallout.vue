<template>
  <q-card
    v-if="!isHidden"
    flat
    bordered
    class="manage-info-callout q-mb-md"
    role="status"
  >
    <q-card-section class="row items-center q-gutter-x-sm callout-header">
      <q-icon :name="icon" size="sm" class="callout-icon" />
      <h3 class="callout-title q-my-none">{{ title }}</h3>
    </q-card-section>
    <q-card-section class="callout-body">
      {{ body }}
    </q-card-section>
    <q-card-actions v-if="dismissKey" align="right" class="callout-actions">
      <q-btn
        flat
        dense
        no-caps
        icon="check"
        class="callout-dismiss"
        :label="$t('guiElements.dismiss')"
        :loading="dismissing"
        @click="onDismiss"
      />
    </q-card-actions>
  </q-card>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Co-located with the only component that fires it. Apollo's
// normalized cache picks up the new dismissed_ui array on the User
// entity, so any consumer reading `currentUser.dismissed_ui` (e.g.
// useUserPreferences().isDismissed) reactively re-evaluates without
// a separate cache write.
graphql(`
  mutation DismissUiElement($key: String!) {
    dismissUiElement(key: $key) {
      id
      dismissed_ui
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { DismissUiElementDocument } from "src/graphql/generated/graphql"
import { useUserPreferences } from "src/use/userPreferences"

// Reusable explainer callout for the manage UI. Two-zone layout:
// a colored header strip with an icon + title, then the body in a
// separate card section so longer copy doesn't compete with the
// title for visual weight. When `dismissKey` is supplied the
// dismissal is persisted server-side against the authenticated
// user — no localStorage, so it follows the user across devices.

interface Props {
  title: string
  body: string
  icon?: string
  dismissKey?: string
}

const props = withDefaults(defineProps<Props>(), {
  icon: "info",
  dismissKey: ""
})

const { isDismissed } = useUserPreferences()

// Local optimistic flag layered over the server-backed `isDismissed`
// computed. Setting it true on click hides the callout immediately
// without waiting for the round-trip; once the mutation resolves
// and the cache catches up, the server-side flag takes over and
// the local one becomes redundant.
const optimisticallyDismissed = ref(false)

const serverDismissed = computed(() =>
  props.dismissKey ? isDismissed(props.dismissKey).value : false
)

const isHidden = computed(
  () => optimisticallyDismissed.value || serverDismissed.value
)

const { mutate: dismissMutation, loading: dismissing } = useMutation(
  DismissUiElementDocument
)

async function onDismiss() {
  if (!props.dismissKey) return
  optimisticallyDismissed.value = true
  try {
    await dismissMutation({ key: props.dismissKey })
  } catch {
    // Roll back the optimistic hide so the user can try again.
    optimisticallyDismissed.value = false
  }
}
</script>

<style scoped>
/* Pale blue backdrop — a soft tint of the info color so the
   callout reads as informational without screaming for attention.
   `!important` is required because Quasar's `.q-card` selector has
   the same specificity as ours and ships its own `background` rule
   (`#fff` in light, `#1d1d1d` in dark) that otherwise wins by
   source order. */
.manage-info-callout {
  /* Material Blue 100 — distinctly blue but still a calm tint so
     the callout doesn't compete with the workflow content below. */
  background-color: rgb(187, 222, 251) !important;
}
.body--dark .manage-info-callout {
  background-color: rgb(38, 70, 110) !important;
}
.callout-icon {
  color: rgba(0, 0, 0, 0.6);
}
.body--dark .callout-icon {
  color: rgba(255, 255, 255, 0.72);
}
/* Compact internal padding so the callout sits as a quiet hint
   above the workflow. Header / body / actions share matching
   horizontal padding and trim vertical breathing room. */
.callout-header {
  padding: 8px 12px 4px;
}
.callout-body {
  padding: 4px 12px 8px;
  font-size: 0.9375rem;
  line-height: 1.45;
}
.callout-actions {
  padding: 0 8px 6px;
  min-height: 0;
}
/* Heading-sized but quieter than a page title. Weight 400 keeps
   the header from competing with bolder section-headings nearby —
   it reads as a friendly explainer label, not a section break. */
.callout-title {
  font-size: 1.25rem;
  font-weight: 400;
  line-height: 1.3;
}
/* Dismiss button reads against the blue panel — `color="dark"`
   was rendering near-black on the deeper dark-mode blue and
   barely readable. Inheriting body-color text in light mode and
   flipping to a light foreground in dark gives proper contrast
   in both. */
.callout-dismiss {
  color: rgba(0, 0, 0, 0.78);
}
.body--dark .callout-dismiss {
  color: rgba(255, 255, 255, 0.88);
}
</style>
