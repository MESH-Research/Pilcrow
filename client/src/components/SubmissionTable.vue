<template>
  <q-table
    bordered
    flat
    square
    :columns="cols"
    :rows="tableData"
    row-key="id"
    :filter="status_filter"
    dense
  >
    <template #top>
      <div class="row full-width justify-between q-pb-md">
        <div class="column">
          <h3 class="q-my-none">
            {{ $t(`submission_tables.${role}.title`) }}
          </h3>
          <!-- eslint-disable-next-line vue/no-v-html -->
          <p class="q-mb-none" v-html="$t(byline, byline_opts)"></p>
        </div>
        <div class="column">
          <q-select
            v-model="status_filter"
            clearable
            square
            outlined
            dense
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
      <div class="full-width row flex-center text--grey q-py-lg">
        <p class="text-h3">
          {{ $t(`submission_tables.type.${tableType}.no_data`) }}
        </p>
      </div>
    </template>
    <template #body="p">
      <q-tr :props="p">
        <q-td key="id" :props="p">
          {{ p.row.id }}
        </q-td>
        <q-td key="title" :props="p">
          <router-link
            :to="{
              name: 'submission_review',
              params: { id: p.row.id },
            }"
            >{{ p.row.title }}
          </router-link>
        </q-td>
        <q-td key="publication" :props="p">
          <router-link
            :to="{
              name: 'publication:home',
              params: { id: p.row.publication.id },
            }"
            >{{ p.row.publication.name }}
          </router-link>
        </q-td>
        <q-td key="status" :props="p">
          {{ $t(`submission.status.${p.row.status}`) }}
        </q-td>
        <q-td key="actions" :props="p">
          <submission-table-actions
            :submission="p.row"
            :action-type="tableType"
            flat
          />
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
<script setup>
import SubmissionTableActions from "./SubmissionTableActions.vue"
import { useI18n } from "vue-i18n"
import { ref, computed } from "vue"
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
  role: {
    type: String,
    default: "reviewer",
  },
})
const status_filter = ref(null)
const unique = (items) => [...new Set(items)]
const unique_statuses = computed(() => {
  return unique(props.tableData.map((item) => item.status))
})
const byline = `submission_tables.${props.role}.byline`
const byline_opts = {
  type_name: t(`submission_tables.type.${props.tableType}.name`),
}
const cols = [
  {
    name: "id",
    field: "id",
    label: t(`submission_tables.columns.number`),
    sortable: true,
    style: "width: 95px",
    align: "right",
  },
  {
    name: "title",
    field: "title",
    label: t(`submission_tables.columns.title`),
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
</script>
