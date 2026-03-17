import { computed, ref, toValue, watch, type MaybeRefOrGetter } from "vue"
import { useQuery } from "@vue/apollo-composable"
import type { DocumentNode } from "graphql"
import type { QueryTableFragment } from "src/graphql/generated/graphql"
interface TablePagination {
  sortBy: string
  descending: boolean
  page: number
  rowsPerPage: number
  rowsNumber: number
}
import { useQueryCapabilities } from "./useQueryCapabilities"

interface UsePaginatedQueryOptions {
  variables?: MaybeRefOrGetter<Record<string, unknown>>
  field?: MaybeRefOrGetter<string>
}

type PaginatedField = QueryTableFragment & { data: Record<string, unknown>[] }

function getField(
  resultData: Record<string, unknown>,
  field: string
): PaginatedField | null {
  if (!resultData) {
    return null
  }
  const value = field
    ? field
        .split(".")
        .reduce<unknown>(
          (o, i) => (o as Record<string, unknown>)?.[i],
          resultData
        )
    : resultData[Object.keys(resultData)[0]]
  return (value as PaginatedField) ?? null
}

export function usePaginatedQuery(
  query: DocumentNode,
  options: UsePaginatedQueryOptions = {}
) {
  const { searchable, rowsPerPageOptions, enablePagination } =
    useQueryCapabilities(query)

  const pagination = ref<TablePagination>({
    sortBy: "",
    descending: false,
    page: 1,
    rowsPerPage: 25,
    rowsNumber: 0
  })

  const page = computed({
    set(value: number | string) {
      const parsed = typeof value === "string" ? parseInt(value) : value
      if (parsed > 0) {
        pagination.value.page = parsed
      }
    },
    get: () => pagination.value.page
  })

  const filter = ref("")

  const queryVariables = computed(() => {
    return {
      ...(pagination.value.sortBy
        ? {
            orderBy: [
              {
                column: pagination.value.sortBy.toUpperCase(),
                order: pagination.value.descending ? "DESC" : "ASC"
              }
            ]
          }
        : {}),
      ...toValue(options.variables),
      first: pagination.value.rowsPerPage,
      page: pagination.value.page,
      ...(searchable.value ? { search: filter.value } : {})
    }
  })

  const { result, refetch, loading } = useQuery(query, queryVariables)

  const rows = ref<Record<string, unknown>[]>([])

  watch(
    result,
    (newValue) => {
      if (newValue) {
        const fieldData = getField(
          newValue as Record<string, unknown>,
          toValue(options.field) ?? ""
        )
        pagination.value.rowsNumber = fieldData?.paginatorInfo?.total ?? 0
        pagination.value.page = fieldData?.paginatorInfo?.currentPage ?? 1
        pagination.value.rowsPerPage = fieldData?.paginatorInfo?.perPage ?? 25
        rows.value = fieldData?.data ?? []
      }
    },
    { immediate: true }
  )

  const onRequest = (reqProps: {
    pagination: {
      sortBy: string
      descending: boolean
      page: number
      rowsPerPage: number
      rowsNumber?: number
    }
    filter?: unknown
    getCellValue: (col: unknown, row: unknown) => unknown
  }) => {
    const { page: p, rowsPerPage, sortBy, descending } = reqProps.pagination
    pagination.value.page = p ?? 1
    pagination.value.rowsPerPage = rowsPerPage ?? 25
    pagination.value.sortBy = sortBy
    pagination.value.descending = descending ?? false
  }

  const paginationModel = computed({
    get: () => (enablePagination.value ? pagination.value : undefined),
    set: (value) => {
      if (value && enablePagination.value) {
        pagination.value = value
      }
    }
  })

  return {
    searchable,
    rowsPerPageOptions,
    paginationModel,
    page,
    filter,
    rows,
    loading,
    refetch,
    onRequest
  }
}
