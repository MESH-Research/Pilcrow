<template>
  <q-card-section>
    <div class="text-weight-bold">
      {{ $t("admin.users.details.submissions.filters.publication") }}
    </div>
    <q-select
      v-model="publication"
      :options="selectOptions"
      :loading="loading"
      emit-value
      map-options
      :clearable="true"
      label="Select a publication"
      @virtual-scroll="onScroll"
    />
  </q-card-section>
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import { useQuery } from "@vue/apollo-composable"
import gql from "graphql-tag"

const publication = defineModel<string | null>({ default: null })

const variables = ref({
  page: 1,
  first: 15
})

const PUBLICATIONS_DROPDOWN = gql`
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

interface PublicationData {
  id: string
  name: string
}

interface PaginatorInfo {
  count: number
  currentPage: number
  hasMorePages: boolean
}

interface PublicationsResult {
  publications: {
    data: PublicationData[]
    paginatorInfo: PaginatorInfo
  }
}

const { result, loading, fetchMore } = useQuery<PublicationsResult>(
  PUBLICATIONS_DROPDOWN,
  variables
)

const pageInfo = computed(() => {
  return result.value?.publications?.paginatorInfo
})

const selectOptions = computed(() => {
  return (
    result.value?.publications.data.map((o) => ({
      label: o.name,
      value: o.id
    })) ?? []
  )
})

function onScroll({ to }: { to: number }) {
  const lastIndex = selectOptions.value.length - 1
  if (!loading.value && pageInfo.value?.hasMorePages && to === lastIndex) {
    fetchMore({
      variables: {
        page: (pageInfo.value?.currentPage ?? 0) + 1,
        first: variables.value.first
      },
      updateQuery: (prev, { fetchMoreResult }) => {
        if (!fetchMoreResult) return prev
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
