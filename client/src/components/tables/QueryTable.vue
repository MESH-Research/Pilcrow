<template>
  <q-table
    v-bind="tableProps"
    v-model:pagination="paginationModel"
    flat
    binary-state-sort
    table-class="q-table--bordered"
    :table-header-class="headerClass"
    :rows="rows"
    :columns="tColumns"
    :visible-columns="props.visibleColumns"
    :grid="props.grid"
    :loading="loading"
    :rows-per-page-options
    v-on="rowClickListeners"
    @request="onRequest"
  >
    <template #top="scope">
      <slot name="top">
        <div class="col q-pa-none">
          <div class="row q-gutter-sm q-pa-none">
            <slot name="top-before" v-bind="scope" />
            <q-input
              v-if="searchable"
              v-model="filter"
              class="col"
              :dense="props.dense"
              debounce="300"
              :placeholder="t(searchKey)"
              clearable
              outlined
              :bottom-slots="false"
              :aria-describedby="props.searchHint ? searchHintId : undefined"
            >
              <template #prepend>
                <q-icon name="search" />
              </template>
            </q-input>

            <slot name="top-extra" v-bind="scope" />
            <q-btn
              v-if="props.newTo"
              color="primary"
              :dense="props.dense"
              :to="newTo"
            >
              {{ t(createKey) }}
            </q-btn>
            <q-btn
              v-else-if="props.onNew"
              :dense="props.dense"
              color="primary"
              @click="$emit('new')"
            >
              {{ t(createKey) }}
            </q-btn>
            <q-btn
              v-if="refreshBtn"
              flat
              dense
              icon="refresh"
              :aria-label="t(refreshKey)"
              @click="refetch()"
            />
            <q-separator v-if="slots['top-after']" vertical inset />
            <slot name="top-after" v-bind="scope" />
          </div>
          <div
            v-if="searchable && props.searchHint"
            :id="searchHintId"
            class="text-caption text-grey-7 q-mt-xs"
          >
            {{ props.searchHint }}
          </div>
        </div>
      </slot>
    </template>
    <template #loading>
      <q-inner-loading showing color="primary" />
    </template>
    <template v-for="(_, slotName) in slots" #[slotName]="data">
      <slot :name="slotName" v-bind="data"></slot>
    </template>
    <template
      v-for="column in compColumns"
      #[`body-cell-${column.name}`]="scope"
      :key="`body-cell-${column.name}`"
    >
      <component
        :is="column.component"
        v-if="column.component"
        :scope="scope"
        :dense="props.dense"
      />
    </template>
  </q-table>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"
import type { Component } from "vue"
import type { QTableProps } from "quasar"

type QTableColumn = NonNullable<QTableProps["columns"]>[number]

// QueryTable resolves header labels exclusively from
// `${tPrefix}.headers.${column.name}`. Quasar's required `label`
// field is omitted from the public column type — every column must
// have a matching i18n entry. Cell-specific column options live on
// the cell component (e.g. WithAsideColumn, NameAvatarColumn) and
// callers union them into the columns array as needed.
export interface QueryTableColumn extends Omit<QTableColumn, "label"> {
  component?: Component
}

export interface QTableBodyCellScope<TCol = QTableColumn> {
  col: TCol
  value: unknown
  row: Record<string, unknown>
  dense?: boolean
}

graphql(`
  fragment QueryTable on Paginator {
    paginatorInfo {
      count
      currentPage
      lastPage
      perPage
      total
    }
  }
`)
</script>

<script setup lang="ts">
import { omit } from "lodash"
import { computed, getCurrentInstance, useId, useSlots } from "vue"
import type { DocumentNode } from "graphql"
import { useQuasar } from "quasar"
import { useI18nPrefix } from "src/use/i18nPrefix"
import { usePaginatedQuery } from "./usePaginatedQuery"
import { useUrlPaginationSync } from "./useUrlPaginationSync"

// i18n keys QueryTable resolves for its built-in chrome (search
// input, create button, refresh button). Callers can override any
// of these by passing a different key via the `labels` prop.
export interface QueryTableLabelKeys {
  search?: string
  create?: string
  refresh?: string
}

const DEFAULT_LABELS: Required<QueryTableLabelKeys> = {
  search: "queryTable.search.placeholder",
  create: "buttons.create",
  refresh: "buttons.refresh"
}

interface QueryTableProps {
  query: DocumentNode
  field?: string
  newTo?: Record<string, unknown>
  columns?: QueryTableColumn[]
  tPrefix?: string
  onNew?: () => void
  variables?: Record<string, unknown>
  refreshBtn?: boolean
  dense?: boolean
  syncUrl?: boolean
  defaultSort?: { sortBy: string; descending?: boolean }
  searchHint?: string
  visibleColumns?: string[]
  grid?: boolean
  labels?: QueryTableLabelKeys
}

const props = withDefaults(defineProps<QueryTableProps>(), {
  field: "",
  newTo: undefined,
  columns: undefined,
  tPrefix: "",
  onNew: undefined,
  variables: () => ({}),
  refreshBtn: true,
  dense: false,
  syncUrl: false,
  defaultSort: undefined,
  searchHint: "",
  visibleColumns: undefined,
  grid: false,
  labels: () => ({})
})

interface Emits {
  new: []
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  "row-click": [evt: Event, row: any, index: number]
}
const emit = defineEmits<Emits>()

function onRowClickHandler(evt: Event, row: unknown, index: number) {
  emit("row-click", evt, row, index)
}

// Only forward row-click to q-table when the parent actually listens
// for it; otherwise Quasar would render a pointer cursor on every row
// even when nothing happens on click.
const instance = getCurrentInstance()
const rowClickListeners = computed(() =>
  instance?.vnode.props?.onRowClick !== undefined
    ? { rowClick: onRowClickHandler }
    : {}
)

const tableProps = computed(() =>
  omit(props, [
    "query",
    "field",
    "variables",
    "newTo",
    "columns",
    "tPrefix",
    "onNew",
    "searchable",
    "timeRange"
  ])
)

const {
  searchable,
  rowsPerPageOptions,
  paginationModel,
  pagination,
  page,
  filter,
  rows,
  loading,
  refetch,
  onRequest,
  result
} = usePaginatedQuery(props.query, {
  variables: computed(() => props.variables ?? {}),
  field: computed(() => props.field),
  defaultSort: props.defaultSort
})

if (props.syncUrl) {
  useUrlPaginationSync({ page, filter, pagination })
}

const { pt, pte, t } = useI18nPrefix(computed(() => props.tPrefix))

const searchKey = computed(() => props.labels.search ?? DEFAULT_LABELS.search)
const createKey = computed(() => props.labels.create ?? DEFAULT_LABELS.create)
const refreshKey = computed(
  () => props.labels.refresh ?? DEFAULT_LABELS.refresh
)
const slots = useSlots()
const searchHintId = `search-hint-${useId()}`

// In dark mode use a slight lift over the page background ($dark)
// to mirror how bg-grey-2 lifts off white in light mode. Plain
// grey-9 reads as cool/neutral against the blue-tinted page.
const $q = useQuasar()
const headerClass = computed(
  () => `${$q.dark.isActive ? "bg-dark-1" : "bg-grey-2"}`
)

// Resolve every column header from `${tPrefix}.headers.${column.name}`.
// Missing keys fall back to the key string (vue-i18n default) and
// emit a console warning in dev so the missing translation is loud.
// Setting Quasar's native `label` here (instead of a `header-cell`
// slot override) means grid mode, mobile body labels, and aria all
// receive the translated string for free.
const tColumns = computed(() => {
  if (props.columns === undefined) {
    return undefined
  }
  if (!props.columns?.length) {
    return []
  }
  return props.columns.map((c) => {
    if (import.meta.env.DEV && !pte(`headers.${c.name}`)) {
      console.warn(
        `[QueryTable] missing header translation: ` +
          `${props.tPrefix}.headers.${c.name}`
      )
    }
    return { ...c, label: pt(`headers.${c.name}`) }
  })
})

const compColumns = computed(() => {
  if (!props.columns?.length) {
    return []
  }
  return props.columns.filter(
    (c): c is QueryTableColumn & { component: Component } => !!c.component
  )
})

defineExpose({
  refetch,
  rows,
  page,
  result,
  pagination
})

const topShow = computed(() => {
  return props.refreshBtn ||
    searchable.value ||
    props.newTo ||
    slots.top ||
    props.onNew
    ? "flex"
    : "none"
})
</script>

<style scoped>
:deep(.q-table__top) {
  padding: 12px 0;
  display: v-bind("topShow");
}
:deep(.q-table thead th) {
  font-weight: 700;
}
</style>
