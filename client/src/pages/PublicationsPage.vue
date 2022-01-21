<template>
  <div v-if="publications">
    <h2 class="q-pl-lg">Publications</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>All Publications</h3>
        <ol class="scroll" data-cy="publications_list">
          <li
            v-for="publication in publications.data"
            :key="publication.id"
            class="q-pa-none"
          >
            <q-item>
              {{ publication.name }}
            </q-item>
          </li>
        </ol>
        <div
          v-if="publications.data.length == 0"
          data-cy="no_publications_message"
        >
          No Publications Created
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { useQuery, useResult } from "@vue/apollo-composable"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import { ref } from "vue"
const current_page = ref(1)

const { result } = useQuery(GET_PUBLICATIONS, { page: current_page.value })
const publications = useResult(result)
</script>
