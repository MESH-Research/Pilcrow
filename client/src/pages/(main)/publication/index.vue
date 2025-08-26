<template>
  <h2 class="q-px-lg">{{ $t("publication.entity", { count: 2 }) }}</h2>
  <section>
    <div v-if="!loading" class="column items-center">
      <q-list
        v-if="publications.length !== 0"
        class="full-width"
        separator
        data-cy="publications_list"
      >
        <q-item
          v-for="publication in publications"
          :key="publication.id"
          class="q-px-lg"
          :to="destRoute(publication.id)"
        >
          <q-item-section>
            <q-item-label>
              {{ publication.name }}
            </q-item-label>
            <q-item-label caption>
              {{ strip(publication.home_page_content) }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
      <q-pagination
        v-if="paginatorInfo"
        v-bind="binds"
        class="q-pa-lg"
        v-on="listeners"
      />
    </div>
    <div v-else class="spinner">
      <q-spinner color="primary" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { GetPublicationsDocument } from "src/gql/graphql"
import { usePagination } from "src/use/pagination"
import type { RouteLocationRaw } from "vue-router"

definePage({
  name: "publication:index"
})

const pubsPaginator = usePagination(GetPublicationsDocument)
const {
  binds,
  listeners,
  data: publications,
  paginatorInfo,
  query: { loading }
} = pubsPaginator

const destRoute = (id: string) => {
  return {
    name: "publication:home",
    params: { id }
  } as RouteLocationRaw
}

function strip(html) {
  const doc = new DOMParser().parseFromString(html, "text/html")
  const text = doc.body.textContent || ""
  return text.length < 200 ? text : text.substring(0, 200) + "..."
}
//TODO: Replace caption snippet with its own description field
</script>

<script lang="ts">
graphql(`
  query GetPublications($page: Int, $first: Int) {
    publications(page: $page, first: $first) {
      paginatorInfo {
        ...PaginationFields
      }
      data {
        id
        name
        home_page_content
      }
    }
  }
`)
</script>
