<template>
  <div>
    <h2 class="q-pl-lg">Publications</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>
          All Publications
          <q-spinner v-if="loading" />
        </h3>
        <div class="column q-gutter-md">
          <q-list
            v-if="publications.length !== 0"
            class="scroll col"
            separator
            bordered
            data-cy="publications_list"
          >
            <q-item v-for="publication in publications" :key="publication.id">
              <q-item-section>
                {{ publication.name }}
              </q-item-section>
            </q-item>
          </q-list>
          <q-pagination v-bind="binds" class="col" v-on="listeners" />
          <div
            v-if="paginatorInfo.count == 0"
            data-cy="no_publications_message"
          >
            No Publications Created
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { GET_PUBLICATIONS } from "src/graphql/queries"
import { usePagination } from "src/use/pagination"

const {
  binds,
  listeners,
  data: publications,
  paginatorInfo,
  loading,
} = usePagination(GET_PUBLICATIONS)
</script>
//TODO: Needs translation
