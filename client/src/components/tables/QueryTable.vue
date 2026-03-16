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

<script setup lang="ts">
import { omit } from "lodash"
import {
  computed,
  ref,
  useSlots,
  onMounted,
  watch,
  defineAsyncComponent,
  type Component
} from "vue"
import { useQuery } from "@vue/apollo-composable"
import { useI18nPrefix } from "src/use/i18nPrefix"
import type {
  QueryTableProps,
  QueryTableColumn,
  TablePagination,
  PaginatedResult
} from "./types"
import type { DocumentNode, OperationDefinitionNode } from "graphql"

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

const queryVariables = computed(() => {
  const opDef = (props.query as DocumentNode).definitions?.find(
    (d): d is OperationDefinitionNode => d.kind === "OperationDefinition"
  )
  return opDef?.variableDefinitions?.map((d) => d.variable.name.value) ?? []
})

const searchable = computed(() => queryVariables.value.includes("search"))

const rowsPerPageOptions = computed(() =>
  queryVariables.value.includes("first") ? [10, 25, 50, 100] : []
)

const enablePagination = computed(() => queryVariables.value.includes("page"))

const paginationModel = computed({
  get: () => (enablePagination.value ? pagination.value : undefined),
  set: (value) => {
    if (value && enablePagination.value) {
      pagination.value = value
    }
  }
})

const { pt } = useI18nPrefix(computed(() => props.tPrefix))

const slots = useSlots()

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

const variables = computed(() => {
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
    ...props.variables,
    first: pagination.value.rowsPerPage,
    page: pagination.value.page,
    ...(searchable.value ? { search: filter.value } : {})
  }
})

const {
  result,
  refetch,
  loading: queryLoading
} = useQuery(props.query, variables)

const loading = ref(true)

watch(queryLoading, (newValue) => (loading.value = newValue))

onMounted(() => {
  if (!loading.value) {
    refetch()
  }
})

const rows = ref<Record<string, unknown>[]>([])

watch(result, (newValue) => {
  if (newValue) {
    const field = getField(newValue) as PaginatedResult | null
    pagination.value.rowsNumber = field?.paginatorInfo.total ?? 0
    pagination.value.page = field?.paginatorInfo.currentPage ?? 1
    pagination.value.rowsPerPage = field?.paginatorInfo.perPage ?? 25
    rows.value = field?.data ?? []
  }
})

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
  const { page, rowsPerPage, sortBy, descending } = reqProps.pagination

  pagination.value.page = page ?? 1
  pagination.value.rowsPerPage = rowsPerPage ?? 25
  pagination.value.sortBy = sortBy
  pagination.value.descending = descending ?? false
}

function getField(resultData: Record<string, unknown>): unknown | null {
  if (!resultData) {
    return null
  }
  if (!props.field) {
    return resultData[Object.keys(resultData)[0]]
  }

  return props.field
    .split(".")
    .reduce<unknown>((o, i) => (o as Record<string, unknown>)?.[i], resultData)
}

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
