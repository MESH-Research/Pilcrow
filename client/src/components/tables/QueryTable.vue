<template>
  <q-table
    v-bind="tableProps"
    v-model:pagination="paginationModel"
    flat
    binary-state-sort
    table-class="q-table--bordered"
    table-header-class="bg-accent text-white"
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
              placeholder="Search"
              clearable
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
              {{ pt("btn.create") }}
            </q-btn>
            <q-btn
              v-else-if="props.onNew"
              :dense="props.dense"
              color="primary"
              @click="$emit('new')"
            >
              {{ pt("btn.create") }}
            </q-btn>
            <q-btn
              v-if="refreshBtn"
              flat
              dense
              icon="refresh"
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
    <template #header-cell="scope">
      <q-th :props="scope">
        {{ pt(`headers.${scope.col.name}`) }}
      </q-th>
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
        :is="getComponent(column.component)"
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
import type { RouteLocationRaw } from "vue-router"

type QTableColumn = NonNullable<QTableProps["columns"]>[number]

export interface QueryTableColumn extends QTableColumn {
  component?: string | Component
  aside?: string | ((row: Record<string, unknown>) => string)
  asideLabel?: string | ((row: Record<string, unknown>) => string)
  /**
   * When provided, cells that support it (e.g. TextCell) render their
   * content as a router-link to the returned route.
   */
  linkTo?: (row: Record<string, unknown>) => RouteLocationRaw
  /**
   * For NameAvatarCell: suppress the username caption below the
   * display name (e.g. when a dedicated username column is present).
   */
  hideUsername?: boolean
}

export interface QTableBodyCellScope {
  col: QTableColumn
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
import {
  computed,
  getCurrentInstance,
  useId,
  useSlots,
  defineAsyncComponent
} from "vue"
import type { DocumentNode } from "graphql"
import { useI18nPrefix } from "src/use/i18nPrefix"
import { usePaginatedQuery } from "./usePaginatedQuery"
import { useUrlPaginationSync } from "./useUrlPaginationSync"

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
  grid: false
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

const { pt } = useI18nPrefix(computed(() => props.tPrefix))
const slots = useSlots()
const searchHintId = `search-hint-${useId()}`

const tColumns = computed(() => {
  if (props.columns === undefined) {
    return undefined
  }
  if (!props.columns?.length) {
    return []
  }
  return props.columns
})

const compColumns = computed(() => {
  if (!props.columns?.length) {
    return []
  }
  return props.columns.filter(
    (c): c is QueryTableColumn & { component: string | Component } =>
      !!c.component
  )
})

function getComponent(component: string | Component): Component {
  return typeof component === "string"
    ? defineAsyncComponent(() => import(`./rows/${component}Row.vue`))
    : component
}

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
</style>
