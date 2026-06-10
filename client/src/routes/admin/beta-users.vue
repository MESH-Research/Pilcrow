<template>
  <div class="q-px-lg">
    <h2>{{ $t("admin.beta_users.title") }}</h2>
    <p class="text-body1 text-grey-8 q-mb-md">
      {{ $t("admin.beta_users.intro") }}
    </p>

    <div class="row items-start q-gutter-x-sm q-mb-lg">
      <FindUserSelect
        v-model="selectedUser"
        class="col"
        data-cy="beta_user_add_select"
      />
      <q-btn
        color="primary"
        no-caps
        style="height: 56px"
        :loading="adding"
        :disable="!selectedUserId"
        :label="$t('admin.beta_users.add_btn')"
        data-cy="beta_user_add_btn"
        @click="addBetaUser"
      />
    </div>
  </div>

  <QueryTable
    ref="table"
    class="q-px-lg q-mt-md"
    :query="GetBetaUsersDocument"
    t-prefix="admin.beta_users"
    :columns="columns"
    :search-hint="$t('admin.beta_users.search_hint')"
    sync-url
    :default-sort="{ sortBy: 'name' }"
    @row-click="handleRowClick"
  >
    <template #body-cell-actions="scope">
      <q-td :props="scope" @click.stop>
        <q-btn
          flat
          dense
          no-caps
          color="negative"
          icon="remove_circle_outline"
          :label="$t('admin.beta_users.remove_btn')"
          :loading="removingId === scope.row.id"
          :data-cy="`beta_user_remove_${scope.row.id}`"
          @click="removeBetaUser(scope.row.id)"
        />
      </q-td>
    </template>
  </QueryTable>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetBetaUsers(
    $page: Int
    $first: Int
    $search: String
    $orderBy: [QueryUsersOrderByOrderByClause!]
  ) {
    users(
      page: $page
      first: $first
      search: $search
      beta: true
      orderBy: $orderBy
    ) {
      ...QueryTable
      data {
        id
        username
        email
        ...NameAvatarCell
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref, useTemplateRef } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useRouter } from "vue-router"
import QueryTable, {
  type QueryTableColumn
} from "src/components/tables/QueryTable.vue"
import NameAvatarCell, {
  type NameAvatarColumn
} from "src/components/tables/common/NameAvatarCell.vue"
import FindUserSelect, {
  type FindUserSelectValue
} from "src/components/forms/FindUserSelect.vue"
import {
  GetBetaUsersDocument,
  SetUserBetaAccessDocument,
  type GetBetaUsersQuery
} from "src/graphql/generated/graphql"
import { useFeedbackMessages } from "src/use/guiElements"

definePage({
  name: "admin:beta-users",
  meta: {
    crumb: { label: "breadcrumbs.admin.beta_users" }
  }
})

type BetaUserRow = GetBetaUsersQuery["users"]["data"][number]

const columns: (QueryTableColumn | NameAvatarColumn)[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row,
    component: NameAvatarCell,
    hideUsername: true,
    sortable: true
  },
  {
    name: "username",
    align: "left",
    field: "username",
    sortable: true
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true
  },
  {
    name: "actions",
    align: "right",
    field: "id"
  }
]

const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages()
const { push } = useRouter()
const table = useTemplateRef<InstanceType<typeof QueryTable>>("table")

const { mutate: setUserBetaAccess } = useMutation(SetUserBetaAccessDocument)

const selectedUser = ref<FindUserSelectValue>(null)
// FindUserSelect can hold either a chosen user object or a raw search
// string; only a chosen object carries an id we can grant beta to.
const selectedUserId = computed(() =>
  typeof selectedUser.value === "object" && selectedUser.value !== null
    ? selectedUser.value.id
    : null
)

const adding = ref(false)
async function addBetaUser() {
  if (!selectedUserId.value) return
  adding.value = true
  try {
    await setUserBetaAccess({ id: selectedUserId.value, enabled: true })
    selectedUser.value = null
    await table.value?.refetch()
  } catch {
    newStatusMessage("failure", t("admin.beta_users.error"))
  } finally {
    adding.value = false
  }
}

const removingId = ref<string | null>(null)
async function removeBetaUser(id: string) {
  removingId.value = id
  try {
    await setUserBetaAccess({ id, enabled: false })
    await table.value?.refetch()
  } catch {
    newStatusMessage("failure", t("admin.beta_users.error"))
  } finally {
    removingId.value = null
  }
}

function handleRowClick(_evt: Event, row: BetaUserRow) {
  push({ name: "user_details", params: { id: row.id } })
}
</script>
