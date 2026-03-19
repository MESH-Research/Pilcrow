import { watch, type Ref } from "vue"
import { useRoute, useRouter } from "vue-router"

interface UrlSyncableState {
  page: { value: number }
  filter: Ref<string>
  pagination: Ref<{
    sortBy: string
    descending: boolean
    rowsPerPage: number
    page: number
  }>
}

/**
 * Syncs pagination, search, and sort state with URL query parameters.
 *
 * Reads initial state from the URL on setup, then watches for changes
 * and updates the URL via router.replace (no history entries).
 *
 * Query params: ?page=2&perPage=50&search=foo&sortBy=name&sortDir=desc
 */
export function useUrlPaginationSync(state: UrlSyncableState) {
  const route = useRoute()
  const router = useRouter()

  // Read initial state from URL
  const query = route.query
  if (query.page) {
    state.page.value = parseInt(query.page as string) || 1
  }
  if (query.perPage) {
    state.pagination.value.rowsPerPage = parseInt(query.perPage as string) || 25
  }
  if (query.search) {
    state.filter.value = query.search as string
  }
  if (query.sortBy) {
    state.pagination.value.sortBy = query.sortBy as string
  }
  if (query.sortDir) {
    state.pagination.value.descending = query.sortDir === "desc"
  }

  // Write state changes to URL
  watch(
    [
      () => state.pagination.value.page,
      () => state.pagination.value.rowsPerPage,
      () => state.pagination.value.sortBy,
      () => state.pagination.value.descending,
      state.filter
    ],
    ([page, perPage, sortBy, descending, search]) => {
      const newQuery: Record<string, string> = {}

      // Preserve non-pagination params from the current URL
      for (const [key, value] of Object.entries(route.query)) {
        if (
          !["page", "perPage", "search", "sortBy", "sortDir"].includes(key) &&
          value
        ) {
          newQuery[key] = value as string
        }
      }

      if (page > 1) newQuery.page = String(page)
      if (perPage !== 25) newQuery.perPage = String(perPage)
      if (search) newQuery.search = search
      if (sortBy) newQuery.sortBy = sortBy
      if (sortBy && descending) newQuery.sortDir = "desc"

      router.replace({ query: newQuery })
    }
  )
}
