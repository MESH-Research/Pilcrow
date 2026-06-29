<template>
  <div class="q-px-lg">
    <h2>{{ $t("admin_avatar_reports.page_title") }}</h2>
  </div>
  <QueryTable
    ref="table"
    class="q-px-lg"
    :query="GetAvatarReportsDocument"
    t-prefix="admin_avatar_reports"
    :columns="columns"
    sync-url
    :default-sort="{ sortBy: 'created_at', descending: true }"
  >
    <template #body-cell-reported_avatar="scope">
      <q-td :props="scope">
        <q-avatar
          v-if="scope.row.reported_avatar_url"
          rounded
          size="48px"
          data-cy="reported_avatar_snapshot"
        >
          <img
            :src="scope.row.reported_avatar_url"
            :alt="$t('admin_avatar_reports.reported_image')"
          />
        </q-avatar>
        <span
          v-else
          class="text-caption text-grey"
          data-cy="reported_avatar_gone"
        >
          {{ $t("admin_avatar_reports.reported_image_unavailable") }}
        </span>
      </q-td>
    </template>

    <template #body-cell-reason="scope">
      <q-td :props="scope" style="white-space: normal; max-width: 24rem">
        <span v-if="scope.value">{{ scope.value }}</span>
        <span v-else class="text-italic text-grey">
          {{ $t("admin_avatar_reports.no_reason") }}
        </span>
      </q-td>
    </template>

    <template #body-cell-actions="scope">
      <q-td :props="scope">
        <q-btn
          flat
          size="sm"
          color="primary"
          :label="$t('admin_avatar_reports.action_dismiss')"
          :loading="actingRowId === scope.row.id"
          :disable="actingRowId !== null"
          data-cy="avatar_report_dismiss"
          @click="dismiss(scope.row)"
        />
        <q-btn
          size="sm"
          color="negative"
          :label="$t('admin_avatar_reports.action_remove')"
          :disable="actingRowId !== null"
          data-cy="avatar_report_remove"
          class="q-ml-xs"
          @click="confirmRemove(scope.row)"
        />
      </q-td>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Reports are transient: a resolved report is deleted and its decision recorded
// in the moderation audit log, so this query only ever returns pending reports.
graphql(`
  query GetAvatarReports($page: Int, $first: Int) {
    avatarReports(page: $page, first: $first) {
      ...QueryTable
      data {
        id
        reason
        created_at
        reported_avatar_url
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
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { ref, useTemplateRef } from "vue"
import { Dialog, Notify } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell, {
  type NameAvatarColumn
} from "src/components/tables/common/NameAvatarCell.vue"
import RemoveAvatarDialog from "src/components/dialogs/RemoveAvatarDialog.vue"
import DateTimeCell from "src/components/tables/common/DateTimeCell.vue"
import {
  GetAvatarReportsDocument,
  type GetAvatarReportsQuery
} from "src/graphql/generated/graphql"
import {
  DISMISS_AVATAR_REPORT,
  RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR
} from "src/graphql/mutations"
import { useAvatarReportsPendingCount } from "src/use/avatarReports"

definePage({
  name: "admin:avatar_reports",
  meta: {
    crumb: { label: "breadcrumbs.admin.avatar_reports" },
    navigation: {
      label: "admin_avatar_reports.page_title",
      icon: "flag",
      description: "admin.dashboard.avatar_reports_description",
      order: 40
    }
  }
})

type ReportRow = GetAvatarReportsQuery["avatarReports"]["data"][number]

const table = useTemplateRef<InstanceType<typeof QueryTable>>("table")

// The id of the report currently being actioned, so its buttons show a
// spinner and every row's action buttons disable — guarding double-submits.
const actingRowId = ref<string | null>(null)

const { t } = useI18n()

// Drives the admin-nav pending badge; refetched after each action.
const { refetch: refetchPendingCount } = useAvatarReportsPendingCount()

const columns: (QueryTableColumn | NameAvatarColumn)[] = [
  {
    name: "user",
    required: true,
    align: "left",
    field: (row) => (row as ReportRow).user,
    component: NameAvatarCell
  },
  {
    // The exact image that was reported, snapshotted at report time, so a
    // later avatar change doesn't make the queue ambiguous.
    name: "reported_avatar",
    align: "left",
    field: (row) => (row as ReportRow).reported_avatar_url
  },
  {
    name: "reporter",
    align: "left",
    field: (row) => (row as ReportRow).reporter?.display_label ?? "—"
  },
  {
    name: "reason",
    align: "left",
    field: "reason"
  },
  {
    name: "created_at",
    align: "left",
    field: "created_at",
    sortable: true,
    component: DateTimeCell
  },
  {
    name: "actions",
    align: "right",
    field: (row) => row
  }
]

const { mutate: dismissMutation } = useMutation(DISMISS_AVATAR_REPORT)
const { mutate: resolveMutation } = useMutation(
  RESOLVE_AVATAR_REPORT_AND_REMOVE_AVATAR
)

async function dismiss(row: ReportRow) {
  if (actingRowId.value !== null) return
  actingRowId.value = row.id
  try {
    await dismissMutation({ id: row.id })
    void refetchPendingCount()
    void table.value?.refetch()
    Notify.create({
      type: "positive",
      message: t("admin_avatar_reports.dismissed_success")
    })
  } catch {
    Notify.create({
      type: "negative",
      message: t("admin_avatar_reports.failure")
    })
  } finally {
    actingRowId.value = null
  }
}

function confirmRemove(row: ReportRow) {
  Dialog.create({ component: RemoveAvatarDialog }).onOk(
    async ({ blockFutureUploads }: { blockFutureUploads: boolean }) => {
      if (actingRowId.value !== null) return
      actingRowId.value = row.id
      try {
        await resolveMutation({ id: row.id, blockFutureUploads })
        void refetchPendingCount()
        void table.value?.refetch()
        Notify.create({
          type: "positive",
          message: t("admin_avatar_reports.removed_success")
        })
      } catch {
        Notify.create({
          type: "negative",
          message: t("admin_avatar_reports.failure")
        })
      } finally {
        actingRowId.value = null
      }
    }
  )
}
</script>
