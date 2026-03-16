import type { Component } from "vue"
import type { QTableProps } from "quasar"
import type { DocumentNode } from "graphql"

/** Single column definition from Quasar's QTable */
export type QTableColumn = NonNullable<QTableProps["columns"]>[number]

/** Scope object passed to body-cell slots */
export interface QTableBodyCellScope {
  col: QTableColumn
  value: unknown
  row: Record<string, unknown>
  dense?: boolean
}

/** Extended column definition with optional component rendering */
export interface QueryTableColumn extends QTableColumn {
  component?: string | Component
  aside?: string | ((row: Record<string, unknown>) => string)
  asideLabel?: string | ((row: Record<string, unknown>) => string)
}

/** Pagination state for server-side paginated queries */
export interface TablePagination {
  sortBy: string
  descending: boolean
  page: number
  rowsPerPage: number
  rowsNumber: number
}

/** Paginator info returned from GraphQL paginated queries */
export interface PaginatorInfo {
  total: number
  currentPage: number
  perPage: number
}

/** Shape of a paginated GraphQL result field */
export interface PaginatedResult<T = Record<string, unknown>> {
  paginatorInfo: PaginatorInfo
  data: T[]
}

/** Props for the QueryTable component */
export interface QueryTableProps {
  query: DocumentNode
  field?: string
  newTo?: Record<string, unknown>
  columns?: QueryTableColumn[]
  tPrefix?: string
  onNew?: () => void
  variables?: Record<string, unknown>
  refreshBtn?: boolean
  dense?: boolean
}
