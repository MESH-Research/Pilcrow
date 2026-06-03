import { ref, watch, type Ref } from "vue"
import { useRoute, useRouter } from "vue-router"

/**
 * URL-synced submission filter state shared by the admin user-submissions
 * table and the Record of Review table. Both encode status/role/publication
 * filters in the query string, drop them when they equal the default set, and
 * reset the table to page 1 on any change.
 */

/** Minimal shape of the QueryTable instance the filters reset to page 1. */
interface PageResettable {
  page: number
}

export interface UseSubmissionFiltersOptions {
  /** Status values treated as "default" — omitted from the URL when selected in full. */
  defaultStatuses: readonly string[]
  /** Role values treated as "default" — omitted from the URL when selected in full. */
  defaultRoles: readonly string[]
  /**
   * Table ref reset to page 1 whenever a filter changes. Optional so the
   * composable can be used (and tested) without a mounted table.
   */
  tableRef?: Ref<PageResettable | null>
  /**
   * When true, an absent status/role query param seeds the filter with the
   * full default set (Record of Review). When false it starts empty (admin).
   */
  prefillDefaults?: boolean
}

export interface UseSubmissionFiltersReturn {
  statusFilter: Ref<string[]>
  roleFilter: Ref<string[]>
  publicationFilter: Ref<string | null>
}

/** Parse a `[a,b,c]`-encoded (or bare) query value into a string array. */
export function parseList(value: string | string[] | undefined): string[] {
  if (!value) return []
  const str = Array.isArray(value) ? value[0] : value
  if (!str) return []
  const inner = str.startsWith("[") ? str.slice(1, -1) : str
  return inner ? inner.split(",") : []
}

/** Encode a string array as a `[a,b,c]` query value. */
export function formatList(values: string[]): string {
  return `[${values.join(",")}]`
}

function isDefaultSelection(
  values: string[],
  defaults: readonly string[]
): boolean {
  return (
    values.length === defaults.length &&
    values.every((v) => defaults.includes(v))
  )
}

export function useSubmissionFilters(
  options: UseSubmissionFiltersOptions
): UseSubmissionFiltersReturn {
  const { defaultStatuses, defaultRoles, tableRef, prefillDefaults } = options
  const route = useRoute()
  const router = useRouter()

  const initFilter = (
    raw: string | string[] | undefined,
    defaults: readonly string[]
  ): string[] =>
    raw ? parseList(raw as string) : prefillDefaults ? [...defaults] : []

  const statusFilter = ref<string[]>(
    initFilter(route.query.status as string | undefined, defaultStatuses)
  )
  const roleFilter = ref<string[]>(
    initFilter(route.query.roles as string | undefined, defaultRoles)
  )
  const publicationFilter = ref<string | null>(
    (route.query.publication as string) || null
  )

  watch(
    [statusFilter, roleFilter, publicationFilter],
    ([status, roles, publication]) => {
      if (tableRef?.value) {
        tableRef.value.page = 1
      }

      const query: Record<string, string> = { ...route.query } as Record<
        string,
        string
      >

      if (!isDefaultSelection(status, defaultStatuses)) {
        query.status = formatList(status)
      } else {
        delete query.status
      }

      if (!isDefaultSelection(roles, defaultRoles)) {
        query.roles = formatList(roles)
      } else {
        delete query.roles
      }

      if (publication) query.publication = publication
      else delete query.publication

      router.replace({ query })
    }
  )

  return { statusFilter, roleFilter, publicationFilter }
}
