<template>
  <div>
    <h2 class="q-px-lg">Publications</h2>
    <section class="q-pa-lg">
      <div v-if="!loading" class="column q-gutter-md items-center">
        <q-list
          v-if="publications.length !== 0"
          class="full-width"
          separator
          data-cy="publications_list"
        >
          <q-item
            v-for="publication in publications"
            :key="publication.id"
            class="q-py-md"
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
          class="col"
          v-on="listeners"
        />
      </div>
    </section>
  </div>
</template>

<script setup>
import { GET_PUBLICATIONS } from "src/graphql/queries"
import { usePagination } from "src/use/pagination"

const pubsPaginator = usePagination(GET_PUBLICATIONS)
const {
  binds,
  listeners,
  data: publications,
  paginatorInfo,
  query: { loading },
} = pubsPaginator

const destRoute = (id) => ({ name: "publication:home", params: { id } })

function strip(html) {
  let doc = new DOMParser().parseFromString(html, "text/html")
  const text = doc.body.textContent || ""
  return text.length < 200 ? text : text.substring(0, 200) + "..."
}
//TODO: Needs translation
//TODO: Replace caption snippet with its own description field
</script>
