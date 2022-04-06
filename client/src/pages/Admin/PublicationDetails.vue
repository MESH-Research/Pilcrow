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
      </h2>
      <div v-if="publication.is_publicly_visible">
        <p>Public</p>
      </div>
      <div v-else>
        <p>Only visible to users associated with the publication.</p>
      </div>
    </div>
    <div class="row q-col-gutter-md">
      <div class="col-md-8">
        <recent-submissions />
      </div>
      <div class="col-md-4">
        <div class="column q-gutter-md">
          <publication-users :publication="publication" />
          <publication-style-criteria :publication="publication" />
        </div>
      </div>
    </div>
  </article>
</template>

<script setup>
import PublicationUsers from "src/components/PublicationUsers.vue"
import { GET_PUBLICATION } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import RecentSubmissions from "../../components/RecentSubmissions.vue"
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
