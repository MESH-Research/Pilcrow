<template>
  <QueryTable
    ref="queryTableRef"
    v-model:selected="selected"
    class="record-of-review-table"
    :query="query"
    field="currentUser.submissions"
    t-prefix="record_of_review"
    :variables="variables"
    :columns="cols"
    :default-sort="{ sortBy: 'created_at', descending: true }"
    :enabled="filtersEnabled"
    sync-url
    selection="multiple"
    row-key="id"
  >
    <template #item="gridProps">
      <div class="q-pa-sm col-12 col-sm-6 col-lg-4 col-xl-3 column">
        <RecordOfReviewCard
          v-model:selected="gridProps.selected"
          :assignment="gridProps.row"
          class="col"
        />
      </div>
    </template>
    <template #top-after>
      <SubmissionsFilterPanel
        v-model:status-filter="statusFilter"
        v-model:role-filter="roleFilter"
        v-model:publication-filter="publicationFilter"
        :allowed-statuses="post_review_states"
      />
    </template>
    <template #header-cell="scope">
      <q-th :props="scope">
        <span>{{ scope.col.label }}</span>
      </q-th>
    </template>
    <template #no-data>
      <q-banner
        v-if="!filtersEnabled"
        class="bg-warning text-dark full-width"
        rounded
      >
        <template #avatar>
          <q-icon name="warning" />
        </template>
        {{
          statusFilter.length === 0 && roleFilter.length === 0
            ? $t("record_of_review.no_filter.status_and_role")
            : statusFilter.length === 0
              ? $t("record_of_review.no_filter.status")
              : $t("record_of_review.no_filter.role")
        }}
      </q-banner>
      <q-card
        v-else-if="$q.screen.width < 770"
        flat
        bordered
        square
        class="q-pa-lg text-center full-width"
      >
        <p class="text-h3">
          {{ $t(`record_of_review.no_data`) }}
        </p>
      </q-card>
      <div v-else class="full-width row flex-center text--grey q-py-lg">
        <p class="text-h3">
          {{ $t(`record_of_review.no_data`) }}
        </p>
      </div>
    </template>
    <template #header-selection="p">
      <q-checkbox
        v-model="p.selected"
        :aria-label="label_header_checkbox"
        :title="label_header_checkbox"
      />
    </template>
    <template #body-selection="p">
      <q-checkbox
        v-model="p.selected"
        :aria-label="generateCheckboxLabel(p.row.submission)"
        :title="generateCheckboxLabel(p.row.submission)"
      />
    </template>
    <template #body-cell-id="p">
      <q-td :props="p">{{ p.row.submission.id }}</q-td>
    </template>
    <template #body-cell-title="p">
      <WithAsideCell :scope="p" style="white-space: normal">
        <template #value>
          <router-link
            data-cy="submission_link_desktop"
            :to="{
              name: 'submission:view',
              params: { id: p.row.submission.id }
            }"
            >{{ p.row.submission.title }}
          </router-link>
        </template>
        <template #aside>
          {{ $t("record_of_review.aside.publication") }}:
          {{ p.row.submission.publication.name }}
        </template>
      </WithAsideCell>
    </template>
    <template #body-cell-submitters="p">
      <q-td :props="p">
        <div class="column q-gutter-xs">
          <div
            v-for="submitter in p.row.submission.submitters"
            :key="submitter.id"
            class="row items-center no-wrap q-gutter-sm"
          >
            <AvatarImage :user="submitter" size="32px" rounded />
            <div class="column">
              <div v-if="submitter.name">{{ submitter.name }}</div>
              <div v-if="submitter.username" class="text-caption text-grey-8">
                {{ submitter.username }}
              </div>
            </div>
          </div>
        </div>
      </q-td>
    </template>
    <template #body-cell-role="p">
      <q-td :props="p">
        {{ $t(`admin.users.details.roles.${p.row.role}`) }}
      </q-td>
    </template>
    <template #body-cell-status="p">
      <q-td :props="p">
        {{ $t(`submission.status.${p.row.submission.status}`) }}
      </q-td>
    </template>
  </QueryTable>
</template>
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment recordOfReviewRow on SubmissionAssignment {
    id
    role
    submission {
      id
      title
      status
      updated_at
      submitters {
        id
        name
        username
        ...avatarImage
      }
      publication {
        id
        name
      }
    }
  }
`)
</script>

<script setup lang="ts">
import type { DocumentNode } from "graphql"
import type { Submission } from "src/graphql/generated/graphql"
import { useI18n } from "vue-i18n"
import { ref, computed, watch } from "vue"
import { useQuasar } from "quasar"
import { post_review_states } from "src/utils/postReviewStates"
import { useSubmissionFilters } from "src/use/submissionFilters"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import WithAsideCell from "src/components/tables/common/WithAsideCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import RecordOfReviewCard from "src/components/ror/RecordOfReviewCard.vue"
import SubmissionsFilterPanel from "src/pages/Admin/components/SubmissionsFilterPanel.vue"
import { defaultOptions as defaultRoleOptions } from "src/pages/Admin/components/SubmissionsFilterPanelRoles.vue"

const $q = useQuasar()
const { t } = useI18n()

interface Props {
  query: DocumentNode
}
defineProps<Props>()

const selected = defineModel<unknown[]>("selected", { default: () => [] })

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

// Record of Review seeds the filters with the full default set so the table
// shows everything until the user narrows it.
const { statusFilter, roleFilter, publicationFilter } = useSubmissionFilters({
  defaultStatuses: post_review_states,
  defaultRoles: defaultRoleOptions,
  tableRef: queryTableRef,
  prefillDefaults: true
})

const filtersEnabled = computed(
  () => statusFilter.value.length > 0 && roleFilter.value.length > 0
)

// A row selected under one filter set is hidden when a later filter excludes
// it, leaving it stuck in the selection with no checkbox to clear. Reset the
// selection whenever any filter changes so the count never strands rows.
watch([statusFilter, roleFilter, publicationFilter], () => {
  if (selected.value.length > 0) selected.value = []
})

const variables = computed(() => ({
  status: statusFilter.value.filter((s) => post_review_states.includes(s)),
  roles: roleFilter.value.filter((r) => defaultRoleOptions.includes(r)),
  publication: publicationFilter.value ? [publicationFilter.value] : undefined
}))

function generateCheckboxLabel(row: Submission) {
  return t("record_of_review.label_select_review_checkbox", {
    id: row.id,
    title: row.title
  })
}
const label_header_checkbox = t("record_of_review.label_header_checkbox")

const cols: QueryTableColumn[] = [
  {
    name: "id",
    field: (row) => (row.submission as Submission).id,
    sortable: false,
    style: "width: 85px",
    align: "right"
  },
  {
    name: "title",
    field: (row) => (row.submission as Submission).title,
    sortable: true,
    style: "width: 30%",
    align: "left"
  },
  {
    name: "submitters",
    field: (row) => (row.submission as Submission).submitters,
    sortable: false,
    align: "left"
  },
  {
    name: "role",
    field: (row) => row.role,
    sortable: false,
    align: "left"
  },
  {
    name: "status",
    field: (row) => (row.submission as Submission).status,
    sortable: true,
    align: "center"
  },
  {
    name: "updated",
    field: (row) => (row.submission as Submission).updated_at,
    sortable: false,
    align: "left",
    component: DateTimeCell
  }
]

const page = computed({
  get: () => queryTableRef.value?.page ?? 1,
  set: (value: number) => {
    if (queryTableRef.value) {
      queryTableRef.value.page = value
    }
  }
})

defineExpose({ page })
</script>

<style lang="sass">
.record-of-review-table
  th
    border-width: 1px 0
    &:first-child
      border-left-width: 1px
    &:last-child
      border-right-width: 1px
  tbody td
    border-style: solid
    &:first-child
      border-left-width: 1px
    &:last-child
      border-right-width: 1px
  .q-table__bottom
    border-color: rgba(0,0,0,0.12)
    border-style: solid
    border-width: 0 1px 1px

.record-of-review-table.q-table--grid
  .q-table__bottom
    border-width: 0

.body--dark
  .record-of-review-table
    .q-table__bottom
      border-color: rgba(255, 255, 255, 0.28)
</style>
