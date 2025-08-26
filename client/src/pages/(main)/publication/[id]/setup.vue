<template>
  <div class="column">
    <h2 class="q-px-lg" data-cy="publication_details_heading">
      {{ publicationName }}
    </h2>
    <div class="row justify-center items-start content-start q-px-lg">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
          <ChildPages :route="{ name: 'publication:setup', params: { id } }" />
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
import ChildPages from "src/components/ChildPages.vue"
import { setCrumbLabel } from "src/use/breadcrumbs"
definePage({
  name: "publication:setup",
  meta: {
    crumb: {
      label: "Setup",
      to: { name: "publication:setup:basic" }
    }
  }
})

const {
  params: { id }
} = useRoute("publication:setup")
const { result } = useQuery(GetPublicationDocument, { id })

const publication = computed(() => result.value?.publication ?? null)
const publicationName = computed(() => publication.value?.name ?? "")
const noStyleCriteria = computed(
  () => publication.value?.style_criterias.length === 0
)
const route = useRoute()
const router = useRouter()

setCrumbLabel("publication:", publicationName)
const labelKey = (page) => `publication.setup_pages.${page}`
const { t } = useI18n()

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
      ...PublicationSetupBasic
      ...PublicationSetupContent
      ...PublicationSetupStyleCriteria
      ...PublicationSetupForms
      ...PublicationSetupUsers
      effective_role
    }
  }
`)
</script>
