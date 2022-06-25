<template>
  <div class="column">
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('publication.entity', 2)"
          :to="{ name: 'publication:index' }"
        />
        <q-breadcrumbs-el
          :label="publicationName"
          :to="{ name: 'publication:home', params }"
        />
        <q-breadcrumbs-el :label="$t(labelKey('breadcrumb_heading'))" />

        <q-breadcrumbs-el :label="route.meta.name" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-px-lg" data-cy="publication_details_heading">
      {{ publicationName }}
    </h2>
    <div class="row justify-center items-start content-start q-px-lg">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
          <collapse-menu :items="items" />
        </q-card-section>
      </q-card>
      <q-card class="col-sm-9 col-xs-12 no-shadow outline no-border-radius">
        <router-view v-if="publication" :publication="publication" />
      </q-card>
    </div>
  </div>
</template>

<script setup>
import CollapseMenu from "src/components/molecules/CollapseMenu.vue"
import { useQuery } from "@vue/apollo-composable"
import { GET_PUBLICATION } from "src/graphql/queries"
import { computed, watchEffect } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useI18n } from "vue-i18n"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)
const publicationName = computed(() => publication.value?.name ?? "")
const noStyleCriteria = computed(
  () => publication.value?.style_criterias.length === 0
)
const route = useRoute()
const { replace } = useRouter()
const params = { id: props.id }
const labelKey = (page) => `publication.setup_pages.${page}`
const { t } = useI18n()
const items = computed(() => [
  {
    icon: "tune",
    label: t(labelKey("basic")),
    url: {
      name: "publication:setup:basic",
      params,
    },
  },
  {
    icon: "people",
    label: t(labelKey("users")),
    url: {
      name: "publication:setup:users",
      params,
    },
    problem: publication.value?.publication_admins.length === 0,
    problemTooltip: t(labelKey("problems.no_admins")),
  },
  {
    icon: "card_membership",
    label: t(labelKey("criteria")),
    url: {
      name: "publication:setup:criteria",
      params,
    },
    problem: noStyleCriteria.value,
    problemTooltip: t(labelKey("problems.no_criteria")),
  },
  {
    icon: "toc",
    label: t(labelKey("content")),
    url: {
      name: "publication:setup:content",
      params,
    },
  },
])
watchEffect(() => {
  if (publication.value) {
    if (publication.value.effective_role !== "publication_admin") {
      replace("/error403")
    }
  }
})
</script>
