<template>
  <h1 class="text-h2 q-pl-md" data-cy="page_heading">
    {{ $t("labs.page_title") }}
  </h1>
  <div class="q-pa-md">
    <p class="text-body1 q-mb-md">
      {{ $t("labs.intro") }}
    </p>

    <!-- Private features are hidden from users without beta access — the
         server would reject opting in. When nothing is visible the user
         sees a clear empty state. -->
    <q-banner
      v-if="visibleFeatures.length === 0"
      rounded
      class="bg-grey-2"
      data-cy="no_labs_access"
    >
      {{ $t("labs.no_access") }}
    </q-banner>

    <!-- Each feature is its own file-based child route; we don't route to
         them, we render their components stacked here. -->
    <div v-else class="column q-gutter-y-lg">
      <component
        :is="feature.component"
        v-for="feature in visibleFeatures"
        :key="feature.key"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, watch } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useNavigation } from "src/use/navigation"
import { useFeatures } from "src/use/features"

definePage({
  name: "account:labs",
  meta: {
    navigation: {
      icon: "o_science",
      label: "labs.page_title",
      order: 30
    }
  }
})

const route = useRoute()
const router = useRouter()

// Feature children are rendered inline here, never routed to. If one of
// their paths is hit directly (deep link, refresh), normalize back to the
// canonical Labs URL. We don't use a `redirect` on the child routes
// because `router.resolve` follows redirects, which would break the
// `childrenOf` list below.
watch(
  () => route.name,
  (name) => {
    if (name && name !== "account:labs") {
      router.replace({ name: "account:labs" })
    }
  },
  { immediate: true }
)

const { childrenOf } = useNavigation()
const { isBeta } = useFeatures()

// Build the Labs list from the child routes' meta.feature. Private
// features only show for users with beta access; ungated ones always.
const children = childrenOf({ name: "account:labs" })
const visibleFeatures = computed(() =>
  children.value
    .filter((c) => c.meta.feature)
    .filter((c) => !c.meta.feature?.private || isBeta.value)
    .map((c) => ({
      key: c.meta.feature?.key as string,
      order: c.meta.feature?.order ?? Number.POSITIVE_INFINITY,
      component: c.component
    }))
    .sort((a, b) => a.order - b.order)
)
</script>
