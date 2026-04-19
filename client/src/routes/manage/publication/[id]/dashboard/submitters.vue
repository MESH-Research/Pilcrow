<template>
  <QueryTable
    :query="GetPublicationUsersDocument"
    field="publication.users"
    t-prefix="publication.manage.users"
    :columns="columns"
    :variables="{ id, roles: ['submitter'] }"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    :dense="isDense"
    :grid="isGrid"
    @row-click="onRowClick"
  >
    <template v-if="isGrid" #item="gridProps">
      <div class="q-pa-sm col-12 col-sm-6 col-md-4 col-lg-3 column">
        <q-card
          flat
          bordered
          clickable
          class="col cursor-pointer user-grid-card"
          @click="goToDetail(gridProps.row.id)"
        >
          <q-card-section class="row items-center no-wrap q-gutter-md">
            <avatar-image :user="gridProps.row" size="56px" rounded />
            <div class="col column q-gutter-xs" style="min-width: 0">
              <div class="text-weight-medium ellipsis">
                {{ gridProps.row.name || gridProps.row.email }}
              </div>
              <div
                v-if="gridProps.row.username"
                class="text-caption text-grey-7 ellipsis"
              >
                {{ gridProps.row.username }}
              </div>
              <div v-if="gridProps.row.email" class="text-caption ellipsis">
                {{ gridProps.row.email }}
              </div>
            </div>
          </q-card-section>
          <q-separator />
          <q-card-section class="row items-center q-py-sm">
            <span class="col text-body2 text-grey-8">
              {{ $t("publication.manage.users.headers.as_submitter_count") }}
            </span>
            <span class="text-h6 q-mr-sm">
              {{ gridProps.row.as_submitter_count }}
            </span>
          </q-card-section>
        </q-card>
      </div>
    </template>
    <template #top-after>
      <q-btn
        v-if="!isSmallScreen"
        flat
        dense
        no-caps
        :icon="isGrid ? 'table_rows' : 'grid_view'"
        :label="isGrid ? 'Table view' : 'Grid view'"
        :aria-label="isGrid ? 'Switch to table view' : 'Switch to grid view'"
        @click="toggleViewPreference"
      />
    </template>
  </QueryTable>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { useQuasar } from "quasar"
import { useRoute, useRouter } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { GetPublicationUsersDocument } from "src/graphql/generated/graphql"

definePage({
  name: "manage:publication:submitters",
  props: true,
  meta: {
    crumb: {
      label: "Submitters"
    }
  }
})

interface Props {
  id: string
}
const props = defineProps<Props>()

const $q = useQuasar()
const router = useRouter()
const route = useRoute()

const viewPreference = ref<"grid" | null>(
  route.query.view === "grid" ? "grid" : null
)
const isSmallScreen = computed(() => $q.screen.lt.md)
const isGrid = computed(
  () => isSmallScreen.value || viewPreference.value === "grid"
)
const isDense = computed(() => $q.screen.md)

function toggleViewPreference() {
  viewPreference.value = viewPreference.value === "grid" ? null : "grid"
}

watch(viewPreference, (value) => {
  const query: Record<string, string> = { ...route.query } as Record<
    string,
    string
  >
  if (value === "grid") query.view = "grid"
  else delete query.view
  router.replace({ query })
})

function goToDetail(userId: string) {
  router.push({
    name: "manage:publication:submitter",
    params: { id: props.id, userId }
  })
}

function onRowClick(_evt: Event, row: { id: string }) {
  goToDetail(row.id)
}

const columns: QueryTableColumn[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row,
    component: NameAvatarCell,
    sortable: true,
    label: "publication.manage.users.headers.name"
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true,
    label: "publication.manage.users.headers.email"
  },
  {
    name: "as_submitter_count",
    align: "right",
    field: "as_submitter_count",
    sortable: true,
    label: "publication.manage.users.headers.as_submitter_count"
  }
]
</script>

<style scoped>
:deep(.q-table--grid .q-table__top) {
  padding: 0 0 4px 0;
}
:deep(.q-table--grid .q-table__grid-content) {
  background-color: #f5f5f5;
  border-radius: 4px;
}
.user-grid-card:hover {
  border-color: var(--q-primary);
}
</style>

<style>
.body--dark .q-table--grid .q-table__grid-content {
  background-color: #262626;
}
</style>
