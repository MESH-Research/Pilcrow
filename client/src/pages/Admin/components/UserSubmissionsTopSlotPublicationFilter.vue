<template>
  <q-card-section>
    <div class="text-weight-bold">
      {{ $t("admin.users.details.submissions.filters.publication") }}
    </div>
    <q-select
      v-model="publication"
      :options="options"
      :loading="loading"
      emit-value
      map-options
      :clearable="true"
      label="Select a publication"
      @virtual-scroll="onScroll"
    />
  </q-card-section>
</template>

<script setup>
import { computed, ref } from "vue"
import { useQuery } from "@vue/apollo-composable"
const publication = defineModel({
  type: String
})

const variables = ref({
  page: 1,
  first: 15
})

const { result, loading, fetchMore } = useQuery(QUERY, variables)

const pageInfo = computed(() => {
  return result.value?.publications?.paginatorInfo || {}
})

const options = computed(() => {
  return result.value?.publications.data.map((o) => ({
    label: o.name,
    value: o.id
  }))
})

function onScroll({ to }) {
  const lastIndex = options.value.length - 1
  console.log("onScroll", { to, lastIndex, loading: loading.value })
  if (!loading.value && pageInfo.value.hasMorePages && to === lastIndex) {
    fetchMore({
      variables: {
        page: pageInfo.value.currentPage + 1,
        first: variables.value.first
      },
      updateQuery: (prev, { fetchMoreResult }) => {
        return {
          publications: {
            paginatorInfo: fetchMoreResult.publications.paginatorInfo,
            data: [
              ...prev.publications.data,
              ...fetchMoreResult.publications.data
            ]
          }
        }
      }
    })
  }
}
</script>

<script>
import { gql } from "graphql-tag"
const QUERY = gql`
  query PublicationsDropdown($page: Int!, $first: Int!) {
    publications(page: $page, first: $first) {
      data {
        id
        name
      }
      paginatorInfo {
        count
        currentPage
        hasMorePages
      }
    }
  }
`
</script>
