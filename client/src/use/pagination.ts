//TODO: This composable needs unit tests
import { unref, reactive, computed, watchEffect } from "vue"
import { defaults } from "lodash"
import { useQuery } from "@vue/apollo-composable"
import type { DocumentNode } from "graphql"
import type { OperationVariables } from "@apollo/client/core"
import type { PaginatorInfo } from "src/graphql/generated/graphql"

export interface PaginationOptions {
  variables?: OperationVariables
}

export function usePagination<T = Record<string, unknown>>(
  doc: DocumentNode,
  options?: PaginationOptions
) {
  const opts = defaults(unref(options) || {}, {
    variables: {}
  })

  const vars = reactive(Object.assign({ page: 1 }, opts.variables))

  const query = useQuery(doc, vars)

  const itemData = computed(() => {
    if (query.loading.value || !query.result.value) return [] as T[]
    return extractElement(query.result.value, "data") as T[]
  })

  const paginatorInfo = computed<PaginatorInfo | null>(() => {
    return !query.loading.value && query.result.value
      ? (extractElement(query.result.value, "paginatorInfo") as PaginatorInfo)
      : null
  })

  function updatePage(newValue: number) {
    vars.page = newValue
  }
  const binds = reactive({
    modelValue: vars.page as number,
    min: 1,
    max: 1
  })

  watchEffect(() => {
    if (paginatorInfo.value) {
      binds.max = paginatorInfo.value.lastPage
      binds.modelValue = paginatorInfo.value.currentPage
    }
  })

  const listeners = {
    "update:modelValue": updatePage
  }

  return {
    data: itemData,
    updatePage,
    paginatorInfo,
    binds,
    listeners,
    query: query,
    vars
  }
}

interface PaginatedQueryResult {
  [queryName: string]: { data: unknown[]; paginatorInfo: PaginatorInfo }
}

function extractElement<K extends "data" | "paginatorInfo">(data: PaginatedQueryResult, element: K): PaginatedQueryResult[string][K] {
  const keys = Object.keys(data)
  if (keys.length !== 1) {
    throw "Unable to extract query return (Are you sure this is a paginated query?)"
  }
  return data[keys[0]][element]
}
