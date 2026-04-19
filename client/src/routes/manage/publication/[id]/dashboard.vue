<template>
  <div
    class="publication-dashboard"
    :class="$q.screen.lt.sm ? 'q-px-sm' : 'q-px-lg'"
  >
    <div class="row items-center q-gutter-sm q-mb-sm q-pt-md">
      <h2 class="q-my-none col">
        {{ publication?.name }} {{ $t("publication.dashboard.heading") }}
      </h2>
      <q-btn
        data-cy="manage_review_team_button"
        icon="groups"
        color="primary"
        :to="{ name: 'manage:publication:team', params: { id } }"
      >
        {{ $t("publication.manage.review_team.heading") }}
      </q-btn>
    </div>

    <div v-if="!publication" class="q-pa-lg">{{ $t("loading") }}</div>
    <template v-else>
      <!-- Overall category counts — display only, no filter UI here;
           filter controls live on the Submissions tab. -->
      <div class="row q-col-gutter-md q-mb-md items-stretch">
        <div
          v-for="category in categories"
          :key="category.key"
          class="col-xs-6 col-sm-6 col-md-3 column"
        >
          <q-card class="col">
            <q-card-section
              :class="`bg-${category.color} ${category.textClass} ${category.pattern}`"
            >
              <template v-if="$q.screen.xs">
                <div class="column items-center pattern-text-mask text-center">
                  <div
                    class="text-weight-bold q-mb-xs"
                    style="font-size: 2rem; line-height: 1"
                  >
                    {{ category.total }}
                  </div>
                  <div class="row items-center no-wrap">
                    <q-icon :name="category.icon" size="sm" class="q-mr-sm" />
                    <span
                      class="text-weight-medium"
                      style="font-size: 0.9rem; line-height: 1.3"
                    >
                      {{
                        $t(`publication.dashboard.categories.${category.key}`)
                      }}
                    </span>
                  </div>
                </div>
              </template>
              <template v-else>
                <div class="row items-center no-wrap pattern-text-mask">
                  <q-icon :name="category.icon" size="md" />
                  <q-separator
                    vertical
                    class="q-mx-sm"
                    style="background: currentColor; opacity: 0.5"
                  />
                  <div
                    class="col text-weight-medium"
                    style="font-size: 1rem; line-height: 1.3"
                  >
                    {{ $t(`publication.dashboard.categories.${category.key}`) }}
                  </div>
                  <div
                    class="text-weight-bold q-ml-sm"
                    style="font-size: 2rem; line-height: 1"
                  >
                    {{ category.total }}
                  </div>
                </div>
              </template>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Dashboard tabs: Submissions + Submitters. Detail pages
           (submitter detail, team member detail) render outside
           this layout. -->
      <q-tabs
        :model-value="activeTab"
        active-color="primary"
        indicator-color="primary"
        align="left"
        class="q-mb-md"
        dense
        no-caps
      >
        <q-route-tab
          name="submissions"
          :label="$t('publication.dashboard.tabs.submissions')"
          :to="{ name: 'manage:publication:submissions', params: { id } }"
        />
        <q-route-tab
          name="submitters"
          :label="$t('publication.dashboard.tabs.submitters')"
          :to="{ name: 'manage:publication:submitters', params: { id } }"
        />
      </q-tabs>

      <router-view :id="id" />
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationDashboard($id: ID!) {
    publication(id: $id) {
      id
      name
      submission_status_counts {
        status
        count
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useRoute } from "vue-router"
import {
  statusCategories,
  type StatusCategoryDef
} from "src/pages/Publication/components/statusCategories"
import { GetPublicationDashboardDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:dashboard",
  props: true
})

interface Props {
  id: string
}
const props = defineProps<Props>()

const $q = useQuasar()
const route = useRoute()

// Highlight the active tab based on the currently-matched child route.
const activeTab = computed(() => {
  const name = route.name?.toString() ?? ""
  if (name.endsWith(":submitters")) return "submitters"
  return "submissions"
})

const { result } = useQuery(GetPublicationDashboardDocument, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)

interface StatusCategory extends StatusCategoryDef {
  total: number
}

const statusCountMap = computed(() => {
  const map = new Map<string, number>()
  for (const sc of publication.value?.submission_status_counts ?? []) {
    map.set(sc.status, sc.count)
  }
  return map
})

const categories = computed<StatusCategory[]>(() =>
  statusCategories.map((cat) => {
    const total = cat.statuses.reduce(
      (sum, status) => sum + (statusCountMap.value.get(status) ?? 0),
      0
    )
    return { ...cat, total }
  })
)
</script>
