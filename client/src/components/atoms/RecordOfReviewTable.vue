<template>
  <q-table
    v-model:selected="selectedReviews"
    :columns="cols"
    class="record-of-review-table"
    flat
    :filter-method="filterStatuses"
    :filter="status_filter"
    :rows="tableData"
    row-key="id"
    selection="multiple"
  >
    <template #top>
      <div class="row full-width justify-between q-pb-md">
        <div class="column">
          <h1 class="q-my-none text-h2">
            {{ $t(`record_of_review_table.title`) }}
            <q-icon name="info">
              <q-tooltip>{{ $t(`record_of_review_table.tooltip`) }}</q-tooltip>
            </q-icon>
          </h1>

          <i18n-t
            keypath="record_of_review_table.byline"
            class="q-mb-none"
            tag="p"
            scope="global"
          ></i18n-t>

          <q-select
            v-if="tableData.length"
            v-model="status_filter"
            clearable
            square
            outlined
            dense
            multiple
            hide-selected
            :label="$t(`submission_tables.filter_label`)"
            :options="unique_statuses"
            style="width: 240px"
            class="q-mt-md q-mr-xs"
          >
            <template #selected-item="scope">
              {{ $t(`submission.status.${scope.opt}`) }}
            </template>
            <template #option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section>
                  <q-item-label>
                    {{ $t(`submission.status.${scope.opt}`) }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>
      </div>
    </template>
    <template #no-data>
      <q-card
        v-if="$q.screen.width < 770"
        flat
        bordered
        square
        class="q-pa-lg text-center full-width"
      >
        <p class="text-h3">
          {{ $t(`record_of_review_table.no_data`) }}
        </p>
      </q-card>
      <div v-else class="full-width row flex-center text--grey q-py-lg">
        <p class="text-h3">
          {{ $t(`record_of_review_table.no_data`) }}
        </p>
      </div>
    </template>
    <template #header-selection="p">
      <q-checkbox
        v-model="p.selected"
        :aria-label="header_checkbox_label"
        :title="header_checkbox_label"
      />
    </template>
    <template #body-selection="p">
      <q-checkbox
        v-model="p.selected"
        :aria-label="generateCheckboxLabel(p.row)"
        :title="generateCheckboxLabel(p.row)"
      />
    </template>
    <template #body-cell-title="p">
      <q-td :props="p" data-cy="submission_link_desktop">
        <router-link
          :to="{
            name: submissionLinkName(p.row, p.row.effective_role),
            params: { id: p.row.id }
          }"
          >{{ p.row.title }}
        </router-link>
      </q-td>
    </template>
    <template #body-cell-submitters="p">
      <q-td :props="p"
        ><span>{{ generateSubmitterList(p.row.submitters) }}</span></q-td
      >
    </template>
    <template #body-cell-submitted="p">
      <q-td :props="p"
        ><span>{{ relativeTime(p.row.submitted_at).value }}</span></q-td
      >
    </template>
    <template #body-cell-publication="p">
      <q-td :props="p">
        {{ p.row.publication.name }}
      </q-td>
    </template>
    <template #body-cell-status="p">
      <q-td :props="p">
        {{ $t(`submission.status.${p.row.status}`) }}
      </q-td>
    </template>
  </q-table>
</template>
<script setup lang="ts">
import type { Submission, User } from "src/graphql/generated/graphql"
import { useI18n } from "vue-i18n"
import { ref, computed } from "vue"
import { useQuasar } from "quasar"
import { relativeTime } from "src/use/timeAgo"
import { submissionLinkName } from "src/utils/submissionLinkName"

const $q = useQuasar()

const { t } = useI18n()

interface Props {
  tableData?: Submission[]
}

function generateCheckboxLabel(row: Submission) {
  return `Select review number ${row.id}, ${row.title}`
}
const header_checkbox_label = `Review Selection`

const props = withDefaults(defineProps<Props>(), {
  tableData: () => [],
  selectedReviews: () => []
})

const selectedReviews = ref([])

const status_filter = ref(null)
const filterStatuses = (rows: Readonly<Submission[]>, terms: Array<string>) => {
  return rows.filter((row) => terms.includes(row.status))
}
const unique = (items: Array<string>) => [...new Set(items)]
const unique_statuses = computed(() => {
  return unique(props.tableData.map((item) => item.status))
})
const cols: {
  name: string
  field: string
  label: string
  sortable: boolean
  style?: string
  align: "left" | "right" | "center"
}[] = [
  {
    name: "id",
    field: "id",
    label: t(`submission_tables.columns.number`),
    sortable: true,
    style: "width: 85px",
    align: "right"
  },
  {
    name: "title",
    field: "title",
    label: t(`submission_tables.columns.title`),
    sortable: true,
    style: "width: 30%",
    align: "left"
  },
  {
    name: "submitters",
    field: "submitters",
    label: t(`submission_tables.columns.submitted_by`),
    sortable: true,
    align: "left"
  },
  {
    name: "submitted",
    field: "submitted_at",
    label: t(`submission_tables.columns.submitted_at`),
    sortable: true,
    align: "left"
  },
  {
    name: "publication",
    field: "publication",
    label: t(`submission_tables.columns.publication`),
    sortable: true,
    align: "left",
    style: "width: 10%"
  },
  {
    name: "status",
    field: "status",
    label: t(`submission_tables.columns.status`),
    sortable: true,
    align: "center"
  }
]
function generateSubmitterList(submitters: Array<User>) {
  return submitters.map((submitter) => submitter.display_label).join(", ")
}
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
