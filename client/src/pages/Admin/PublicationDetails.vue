<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el
        :label="$t('header.publications')"
        to="/admin/publications"
      />
      <q-breadcrumbs-el :label="$t('publications.details')" />
    </q-breadcrumbs>
  </nav>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-pa-md">
    <div class="row">
      <h2 class="col-sm-12" data-cy="publication_details_heading">
        {{ publication.name }}

        <div v-if="publication.is_publicly_visible">
          <q-badge>
            Public
            <q-tooltip>This publication is visible to anyone.</q-tooltip>
          </q-badge>
        </div>
        <div v-else>
          <q-badge>
            Private
            <q-tooltip>
              Only visible to users associated with the publication.
            </q-tooltip>
          </q-badge>
        </div>
      </h2>
    </div>
    <div class="column q-gutter-md">
      <publication-users :publication="publication" />
      <publication-style-criteria :publication="publication" />
    </div>
  </article>
</template>

<script setup>
import PublicationUsers from "src/components/PublicationUsers.vue"
import { GET_PUBLICATION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import PublicationStyleCriteria from "src/components/PublicationStyleCriteria.vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result: pubResult } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = useResult(pubResult)
</script>
