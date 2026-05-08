<template>
  <QueryTable
    ref="queryTableRef"
    v-model:selected="selected"
    class="record-of-review-table"
    :query="query"
    field="currentUser.submissions"
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
          Publication: {{ p.row.submission.publication.name }}
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
import { useRoute, useRouter } from "vue-router"
import { useQuasar } from "quasar"
import { post_review_states } from "src/utils/postReviewStates"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import WithAsideCell from "src/components/tables/common/WithAsideCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import RecordOfReviewCard from "src/components/RecordOfReviewCard.vue"
import SubmissionsFilterPanel from "src/pages/Admin/components/SubmissionsFilterPanel.vue"
import { defaultOptions as defaultRoleOptions } from "src/pages/Admin/components/SubmissionsFilterPanelRoles.vue"

const $q = useQuasar()
const { t } = useI18n()

interface Props {
  query: DocumentNode
}
defineProps<Props>()

const selected = defineModel<unknown[]>("selected", { default: () => [] })

const route = useRoute()
const router = useRouter()

function parseList(value: string | string[] | undefined): string[] {
  if (!value) return []
  const str = Array.isArray(value) ? value[0] : value
  if (!str) return []
  const inner = str.startsWith("[") ? str.slice(1, -1) : str
  return inner ? inner.split(",") : []
}

function formatList(values: string[]): string {
  return `[${values.join(",")}]`
}

const statusFilter = ref<string[]>(
  route.query.status
    ? parseList(route.query.status as string)
    : [...post_review_states]
)
const roleFilter = ref<string[]>(
  route.query.roles
    ? parseList(route.query.roles as string)
    : [...defaultRoleOptions]
)
const publicationFilter = ref<string | null>(
  (route.query.publication as string) || null
)

const filtersEnabled = computed(
  () => statusFilter.value.length > 0 && roleFilter.value.length > 0
)

const variables = computed(() => ({
  status: statusFilter.value.filter((s) => post_review_states.includes(s)),
  roles: roleFilter.value.filter((r) => defaultRoleOptions.includes(r)),
  publication: publicationFilter.value ? [publicationFilter.value] : undefined
}))

watch(
  [statusFilter, roleFilter, publicationFilter],
  ([status, roles, publication]) => {
    if (queryTableRef.value) {
      queryTableRef.value.page = 1
    }

    const query: Record<string, string> = { ...route.query } as Record<
      string,
      string
    >

    const isDefaultStatus =
      status.length === post_review_states.length &&
      status.every((s) => post_review_states.includes(s))
    if (!isDefaultStatus) query.status = formatList(status)
    else delete query.status

    const isDefaultRoles =
      roles.length === defaultRoleOptions.length &&
      roles.every((r) => defaultRoleOptions.includes(r))
    if (!isDefaultRoles) query.roles = formatList(roles)
    else delete query.roles

    if (publication) query.publication = publication
    else delete query.publication

    router.replace({ query })
  }
)

const queryTableRef = ref<InstanceType<typeof QueryTable> | null>(null)

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
    label: t(`submission_tables.columns.number`),
    sortable: false,
    style: "width: 85px",
    align: "right"
  },
  {
    name: "title",
    field: (row) => (row.submission as Submission).title,
    label: t(`submission_tables.columns.title`),
    sortable: true,
    style: "width: 30%",
    align: "left"
  },
  {
    name: "submitters",
    field: (row) => (row.submission as Submission).submitters,
    label: t(`submission_tables.columns.submitted_by`),
    sortable: false,
    align: "left"
  },
  {
    name: "role",
    field: (row) => row.role,
    label: t(`submission_tables.columns.role`),
    sortable: false,
    align: "left"
  },
  {
    name: "status",
    field: (row) => (row.submission as Submission).status,
    label: t(`submission_tables.columns.status`),
    sortable: true,
    align: "center"
  },
  {
    name: "updated",
    field: (row) => (row.submission as Submission).updated_at,
    label: t(`submission_tables.columns.updated_at`),
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
