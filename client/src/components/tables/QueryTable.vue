<template>
  <q-table
    v-bind="tableProps"
    v-model:pagination="paginationModel"
    flat
    table-class="q-table--bordered"
    table-header-class="bg-accent text-white"
    :rows="rows"
    :columns="tColumns"
    :loading="loading"
    :rows-per-page-options
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
              icon="refresh"
              :dense="props.dense"
              @click="refetch()"
            />
            <slot name="top-after" v-bind="scope" />
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

type QTableColumn = NonNullable<QTableProps["columns"]>[number]

export interface QueryTableColumn extends QTableColumn {
  component?: string | Component
  aside?: string | ((row: Record<string, unknown>) => string)
  asideLabel?: string | ((row: Record<string, unknown>) => string)
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
import { computed, useSlots, defineAsyncComponent } from "vue"
import type { DocumentNode } from "graphql"
import { useI18nPrefix } from "src/use/i18nPrefix"
import { usePaginatedQuery } from "./usePaginatedQuery"

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
}

const props = withDefaults(defineProps<QueryTableProps>(), {
  field: "",
  newTo: undefined,
  columns: undefined,
  tPrefix: "",
  onNew: undefined,
  variables: () => ({}),
  refreshBtn: true,
  dense: false
})

interface Emits {
  new: []
}
defineEmits<Emits>()

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
  page,
  filter,
  rows,
  loading,
  refetch,
  onRequest
} = usePaginatedQuery(props.query, {
  variables: computed(() => props.variables ?? {}),
  field: computed(() => props.field)
})

const { pt } = useI18nPrefix(computed(() => props.tPrefix))
const slots = useSlots()

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
  page
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
