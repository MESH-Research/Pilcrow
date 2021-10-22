<template>
  <div v-if="$apollo.loading" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.publications')"
          to="/admin/publications"
        />
        <q-breadcrumbs-el :label="$t('publications.details')" />
      </q-breadcrumbs>
    </nav>
    <div class="q-px-lg">
      <h2 class="col-sm-12" data-cy="userDetailsHeading">
        {{ publication.name }}
      </h2>
      <div v-if="publication.is_publicly_visible">
        This publication is not private and is visible to all users in CCR.
      </div>
      <div v-else>
        This publication is private and meant to be invisible to those outside
        of this publication.
      </div>
    </div>
  </article>
</template>

<script>
import { GET_PUBLICATION } from "src/graphql/queries"

export default {
  props: {
    id: {
      type: String,
      required: true,
    },
  },
  apollo: {
    publication: {
      query: GET_PUBLICATION,
      variables() {
        return {
          id: this.id,
        }
      },
    },
  },
}
</script>
