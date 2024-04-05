<template>
  <q-table
    :grid="$q.screen.width < 770"
    flat
    square
    :columns="cols"
    :rows="tableData"
    row-key="id"
    :filter="status_filter"
    :filter-method="filterStatuses"
    class="submission-table"
  >
    <template #top>
      <div class="row full-width justify-between q-pb-md">
        <div class="column">
          <h3 class="q-my-none">
            {{ $t(title) }}
            <q-icon name="info">
              <q-tooltip>{{ $t(tooltip) }}</q-tooltip>
            </q-icon>
          </h3>

          <i18n-t :keypath="byline" class="q-mb-none" tag="p" scope="global">
            <template #role>
              <strong>{{ $t(`role.${role}s`, 1) }}</strong>
            </template>
          </i18n-t>

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
          {{ $t(`submission_tables.type.${tableType}.no_data`) }}
        </p>
      </q-card>
      <div v-else class="full-width row flex-center text--grey q-py-lg">
        <p class="text-h3">
          {{ $t(`submission_tables.type.${tableType}.no_data`) }}
        </p>
      </div>
    </template>
    <template #item="p">
      <q-card flat :props="p" class="full-width">
        <q-card-section horizontal>
          <q-card-section>
            {{ p.row.id }}
          </q-card-section>
          <q-card-section class="full-width" data-cy="submission_link_mobile">
            <div class="row">
              <div class="col">
                <router-link
                  :to="{
                    name: submissionLinkName(p.row),
                    params: { id: p.row.id },
                  }"
                  >{{ p.row.title }}
                </router-link>
                <p class="q-ma-none">{{ generateSubmitterList(p.row.submitters) }}</p>
                <p class="q-ma-none">{{ relativeTime(p.row.created_at).value }}</p>
              </div>
            </div>
            <div class="row justify-between">
              <router-link
                :to="{
                  name: 'publication:home',
                  params: { id: p.row.publication.id },
                }"
                >{{ p.row.publication.name }}
              </router-link>
              <div class="col-grow text-right q-pl-md">
                {{ $t(`submission.status.${p.row.status}`) }}
              </div>
            </div>
          </q-card-section>
          <q-card-section>
            <submission-table-actions
              :submission="p.row"
              :action-type="tableType"
              flat
            />
          </q-card-section>
        </q-card-section>
        <q-separator />
      </q-card>
    </template>
    <template #body-cell-title="p">
      <q-td :props="p" data-cy="submission_link_desktop">
        <router-link
          :to="{
            name: submissionLinkName(p.row),
            params: { id: p.row.id },
          }"
          >{{ p.row.title }}
        </router-link>
      </q-td>
    </template>
    <template #body-cell-submitters="p">
      <q-td :props="p"><span>{{ generateSubmitterList(p.row.submitters) }}</span></q-td>
    </template>
    <template #body-cell-created="p">
      <q-td :props="p"><span>{{ relativeTime(p.row.created_at).value }}</span></q-td>
    </template>
    <template #body-cell-publication="p">
      <q-td :props="p">
        <router-link
          :to="{
            name: 'publication:home',
            params: { id: p.row.publication.id },
          }"
          >{{ p.row.publication.name }}
        </router-link>
      </q-td>
    </template>
    <template #body-cell-status="p">
      <q-td :props="p">
        {{ $t(`submission.status.${p.row.status}`) }}
      </q-td>
    </template>
    <template #body-cell-actions="p">
      <q-td :props="p">
        <submission-table-actions
          :submission="p.row"
          :action-type="tableType"
          flat
        />
      </q-td>
    </template>
  </q-table>
</template>
<script setup>
import SubmissionTableActions from "./SubmissionTableActions.vue"
import { useI18n } from "vue-i18n"
import { ref, computed } from "vue"
import { relativeTime } from "src/use/timeAgo"
const { t } = useI18n()

const props = defineProps({
  tableData: {
    type: Array,
    default: () => [],
  },
  tableType: {
    type: String,
    default: "submissions",
  },
  variation: {
    type: String,
    default: "",
  },
  role: {
    type: String,
    default: "reviewer",
  },
})
const status_filter = ref(null)
const filterStatuses = (rows, terms) => {
  return rows.filter((row) => terms.includes(row.status))
}
const unique = (items) => [...new Set(items)]
const unique_statuses = computed(() => {
  return unique(props.tableData.map((item) => item.status))
})
const title = props.variation
  ? `submission_tables.${props.variation}.${props.role}.title`
  : `submission_tables.${props.role}.title`
const byline = props.variation
  ? `submission_tables.${props.variation}.${props.role}.byline`
  : `submission_tables.${props.role}.byline`
const tooltip = props.variation
  ? `submission_tables.${props.variation}.${props.role}.tooltip`
  : `submission_tables.${props.role}.tooltip`
const submissionLinkName = (submission) => {
  if (props.role !== "submitter" && submission.status === "DRAFT") {
    return "submission:preview"
  } else if (submission.status === "INITIALLY_SUBMITTED") {
    return "submission:view"
  } else if (submission.status === "DRAFT") {
    return "submission:draft"
  }
  return "submission:review"
}
const cols = [
  {
    name: "id",
    field: "id",
    label: t(`submission_tables.columns.number`),
    sortable: true,
    style: "width: 85px",
    align: "right",
  },
  {
    name: "title",
    field: "title",
    label: t(`submission_tables.columns.title`),
    sortable: true,
    style: "width: 30%",
    align: "left",
  },
  {
    name: "submitters",
    field: "submitters",
    label: t(`submission_tables.columns.submitted_by`),
    sortable: true,
    align: "left",
  },
  {
    name: "created",
    field: "created_at",
    label: t(`submission_tables.columns.created_at`),
    sortable: true,
    align: "left",
  },
  {
    name: "publication",
    field: "publication",
    label: t(`submission_tables.columns.publication`),
    sortable: true,
    align: "left",
    style: "width: 10%",
  },
  {
    name: "status",
    field: "status",
    label: t(`submission_tables.columns.status`),
    sortable: true,
    align: "center",
  },
  {
    name: "actions",
    field: "actions",
    label: t(`submission_tables.columns.actions`),
    sortable: false,
    style: "width: 100px",
    align: "center",
  },
]
function generateSubmitterList(submitters) {
  return submitters.map((submitter) => submitter.display_label).join(", ")
}

</script>

<style lang="sass">
.submission-table
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

.submission-table.q-table--grid
  .q-table__bottom
    border-width: 0

.body--dark
  .submission-table
    .q-table__bottom
      border-color: rgba(255, 255, 255, 0.28)
</style>
