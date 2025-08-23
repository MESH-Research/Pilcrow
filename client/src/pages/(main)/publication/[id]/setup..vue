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
          :to="{ name: 'publication:home', params: { id } }"
        />
        <q-breadcrumbs-el :label="$t(labelKey('breadcrumb_heading'))" />

        <q-breadcrumbs-el :label="name" />
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

<script setup lang="ts">
import CollapseMenu from "src/components/molecules/CollapseMenu.vue"
definePage({
  name: "publication:setup"
})

const {
  params: { id },
  name
} = useRoute("/(main)/publication/[id]/setup.")

const { result } = useQuery(GetPublicationDocument, { id })

const publication = computed(() => result.value?.publication ?? null)
const publicationName = computed(() => publication.value?.name ?? "")
const noStyleCriteria = computed(
  () => publication.value?.style_criterias.length === 0
)
const route = useRoute()
const router = useRouter()

const labelKey = (page) => `publication.setup_pages.${page}`
const { t } = useI18n()
const items = computed(() => [
  {
    icon: "tune",
    label: t(labelKey("basic")),
    url: {
      name: "publication:setup:basic",
      params: { id }
    }
  },
  {
    icon: "people",
    label: t(labelKey("users")),
    url: {
      name: "publication:setup:users",
      params: { id }
    },
    problem: publication.value?.publication_admins.length === 0,
    problemTooltip: t(labelKey("problems.no_admins"))
  },
  {
    icon: "card_membership",
    label: t(labelKey("criteria")),
    url: {
      name: "publication:setup:criteria",
      params: { id }
    },
    problem: noStyleCriteria.value,
    problemTooltip: t(labelKey("problems.no_criteria"))
  },
  {
    icon: "ballot",
    label: "Meta Forms",
    url: {
      name: "publication:setup:metaForms",
      params: { id }
    }
  },
  {
    icon: "toc",
    label: t(labelKey("content")),
    url: {
      name: "publication:setup:content",
      params: { id }
    }
  }
])
watchEffect(() => {
  if (publication.value) {
    if (publication.value.effective_role !== PublicationRole.PublicationAdmin) {
      void router.replace("/error403")
    }
  }
})
</script>

<script lang="ts">
graphql(`
  query GetPublication($id: ID!) {
    publication(id: $id) {
      id
      name
      is_publicly_visible
      is_accepting_submissions
      effective_role
      home_page_content
      new_submission_content
      style_criterias {
        name
        id
        icon
        description
      }
      publication_admins {
        ...RelatedUserFields
      }
      editors {
        ...RelatedUserFields
      }
    }
  }
`)
</script>
