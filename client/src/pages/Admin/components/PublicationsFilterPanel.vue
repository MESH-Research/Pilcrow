<template>
  <q-btn
    flat
    dense
    no-caps
    icon="filter_list"
    :label="
      activeCount > 0
        ? $t('admin.filters.active', { count: activeCount })
        : $t('admin.filters.label')
    "
    :aria-label="$t('admin.publication.filters.aria')"
  >
    <q-menu>
      <q-card-section>
        <div class="text-weight-bold q-mb-sm">
          {{ $t("admin.publication.filters.visibility") }}
        </div>
        <q-btn-toggle
          v-model="visibilityFilter"
          :options="visibilityOptions"
          spread
          unelevated
          toggle-color="primary"
          :color="unselectedColor"
          :text-color="unselectedTextColor"
          no-caps
        />
        <q-separator class="q-my-md" />
        <div class="text-weight-bold q-mb-sm">
          {{ $t("admin.publication.filters.accepting_submissions") }}
        </div>
        <q-btn-toggle
          v-model="acceptingFilter"
          :options="acceptingOptions"
          spread
          unelevated
          toggle-color="primary"
          :color="unselectedColor"
          :text-color="unselectedTextColor"
          no-caps
        />
      </q-card-section>
    </q-menu>
  </q-btn>
</template>

<script lang="ts">
export type VisibilityFilter = "all" | "public" | "hidden"
export type AcceptingFilter = "all" | "yes" | "no"

export const defaultVisibility: VisibilityFilter = "all"
export const defaultAccepting: AcceptingFilter = "all"
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"

const { t } = useI18n()
const $q = useQuasar()

const unselectedColor = computed(() => ($q.dark.isActive ? "dark-1" : "grey-3"))
const unselectedTextColor = computed(() =>
  $q.dark.isActive ? "grey-3" : "grey-9"
)

const visibilityFilter = defineModel<VisibilityFilter>("visibilityFilter", {
  default: defaultVisibility
})
const acceptingFilter = defineModel<AcceptingFilter>("acceptingFilter", {
  default: defaultAccepting
})

const visibilityOptions = computed(() => [
  { label: t("admin.filters.all"), value: "all" as const },
  {
    label: t("admin.publication.filters.visibility_options.public"),
    value: "public" as const
  },
  {
    label: t("admin.publication.filters.visibility_options.hidden"),
    value: "hidden" as const
  }
])

const acceptingOptions = computed(() => [
  { label: t("admin.filters.all"), value: "all" as const },
  {
    label: t("admin.publication.filters.accepting_options.yes"),
    value: "yes" as const
  },
  {
    label: t("admin.publication.filters.accepting_options.no"),
    value: "no" as const
  }
])

const activeCount = computed(() => {
  let n = 0
  if (visibilityFilter.value !== defaultVisibility) n++
  if (acceptingFilter.value !== defaultAccepting) n++
  return n
})
</script>
