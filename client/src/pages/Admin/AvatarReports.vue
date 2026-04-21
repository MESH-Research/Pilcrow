<template>
  <div class="q-px-lg">
    <nav class="q-pt-md">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          label="Administration"
          :to="{ name: 'admin:dashboard' }"
        />
        <q-breadcrumbs-el label="Avatar Reports" />
      </q-breadcrumbs>
    </nav>
    <h2>{{ $t("admin_avatar_reports.page_title") }}</h2>
  </div>
  <QueryTable
    class="q-px-lg"
    :query="GetAvatarReportsDocument"
    t-prefix="admin_avatar_reports"
    :variables="queryVariables"
    :columns="columns"
    sync-url
    :default-sort="{ sortBy: 'created_at', descending: true }"
  >
    <template #top-before>
      <q-btn-toggle
        v-model="statusFilter"
        :options="statusOptions"
        toggle-color="primary"
        no-caps
        class="q-mb-md"
        data-cy="avatar_reports_filter"
      />
    </template>

    <template #body-cell-reason="scope">
      <q-td :props="scope" style="white-space: normal; max-width: 24rem">
        <span v-if="scope.value">{{ scope.value }}</span>
        <span v-else class="text-italic text-grey">no reason supplied</span>
      </q-td>
    </template>

    <template #body-cell-status="scope">
      <q-td :props="scope">
        <q-badge :color="statusColor(scope.row.status)">
          {{ scope.row.status }}
        </q-badge>
      </q-td>
    </template>

    <template #body-cell-actions="scope">
      <q-td :props="scope">
        <template v-if="scope.row.status === 'PENDING'">
          <q-btn
            flat
            size="sm"
            color="primary"
            :label="$t('admin_avatar_reports.action_dismiss')"
            data-cy="avatar_report_dismiss"
            @click="dismiss(scope.row)"
          />
          <q-btn
            size="sm"
            color="negative"
            :label="$t('admin_avatar_reports.action_remove')"
            data-cy="avatar_report_remove"
            class="q-ml-xs"
            @click="confirmRemove(scope.row)"
          />
        </template>
        <template v-else>
          <span class="text-caption text-grey-7">
            {{ scope.row.resolver?.display_label ?? "—" }}
          </span>
        </template>
      </q-td>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetAvatarReports($page: Int, $first: Int, $status: AvatarReportStatus) {
    avatarReports(page: $page, first: $first, status: $status) {
      ...QueryTable
      data {
        id
        status
        reason
        resolution_notes
        resolved_at
        created_at
        user {
          id
          display_label
          ...NameAvatarCell
        }
        reporter {
          id
          display_label
          username
        }
        resolver {
          id
          display_label
          username
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref } from "vue"
import { Dialog, Notify } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell from "src/components/tables/common/NameAvatarCell.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import {
  GetAvatarReportsDocument,
  type GetAvatarReportsQuery
} from "src/graphql/generated/graphql"
import {
  DISMISS_AVATAR_REPORT,
  RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR
} from "src/graphql/mutations"

type ReportRow = GetAvatarReportsQuery["avatarReports"]["data"][number]

const { t } = useI18n()
const statusFilter = ref<"PENDING" | "DISMISSED" | "REMOVED" | null>("PENDING")

/**
 * Omit `status` entirely when the filter is "All" — `@eq` in the
 * Lighthouse schema treats an explicit null as `WHERE status IS NULL`,
 * which matches zero rows since status is never null.
 */
const queryVariables = computed(() =>
  statusFilter.value === null ? {} : { status: statusFilter.value }
)

const statusOptions = computed(() => [
  { value: null, label: t("admin_avatar_reports.filter_all") },
  { value: "PENDING", label: t("admin_avatar_reports.filter_pending") },
  { value: "DISMISSED", label: t("admin_avatar_reports.filter_dismissed") },
  { value: "REMOVED", label: t("admin_avatar_reports.filter_removed") }
])

const columns: QueryTableColumn[] = [
  {
    name: "user",
    required: true,
    align: "left",
    field: (row) => (row as ReportRow).user,
    component: NameAvatarCell,
    label: "admin_avatar_reports.headers.user"
  },
  {
    name: "reporter",
    align: "left",
    field: (row) => (row as ReportRow).reporter?.display_label ?? "—",
    label: "admin_avatar_reports.headers.reporter"
  },
  {
    name: "reason",
    align: "left",
    field: "reason",
    label: "admin_avatar_reports.headers.reason"
  },
  {
    name: "status",
    align: "left",
    field: "status",
    label: "admin_avatar_reports.headers.status"
  },
  {
    name: "created_at",
    align: "left",
    field: "created_at",
    sortable: true,
    component: DateTimeCell,
    label: "admin_avatar_reports.headers.created_at"
  },
  {
    name: "actions",
    align: "right",
    field: (row) => row,
    label: "admin_avatar_reports.headers.actions"
  }
]

function statusColor(status: string): string {
  switch (status) {
    case "PENDING":
      return "warning"
    case "REMOVED":
      return "negative"
    default:
      return "grey-6"
  }
}

const { mutate: dismissMutation } = useMutation(DISMISS_AVATAR_REPORT)
const { mutate: resolveMutation } = useMutation(
  RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR
)

async function dismiss(row: ReportRow) {
  try {
    await dismissMutation({ id: row.id })
    Notify.create({
      type: "positive",
      message: t("admin_avatar_reports.dismissed_success")
    })
  } catch {
    Notify.create({
      type: "negative",
      message: t("admin_avatar_reports.failure")
    })
  }
}

function confirmRemove(row: ReportRow) {
  Dialog.create({
    title: t("admin_avatar_reports.action_remove"),
    message: t("admin_avatar_reports.confirm_remove"),
    cancel: true,
    ok: {
      color: "negative",
      label: t("admin_avatar_reports.action_remove")
    }
  }).onOk(async () => {
    try {
      await resolveMutation({ id: row.id })
      Notify.create({
        type: "positive",
        message: t("admin_avatar_reports.removed_success")
      })
    } catch {
      Notify.create({
        type: "negative",
        message: t("admin_avatar_reports.failure")
      })
    }
  })
}
</script>
