<template>
  <q-table bordered flat :columns="cols" :rows="tableData" row-key="id" dense>
    <template #top>
      <div>
        <h3 class="q-my-none">{{ tableTitle }}</h3>
        <!-- eslint-disable-next-line vue/no-v-html -->
        <p v-html="tableByline"></p>
      </div>
    </template>
    <template #no-data>
      <div class="full-width row flex-center text--grey q-py-xl">
        <p class="text-h3">
          There are no {{ type == "reviews" ? "reviews" : "submissions" }} for
          you.
        </p>
      </div>
    </template>
    <template #body="props">
      <q-tr :props="props">
        <q-td key="id" :props="props">
          {{ props.row.id }}
        </q-td>
        <q-td key="title" :props="props">
          <router-link
            :to="{
              name: 'submission_review',
              params: { id: props.row.id },
            }"
            >{{ props.row.title }}
          </router-link>
        </q-td>
        <q-td key="publication" :props="props">
          <router-link
            :to="{
              name: 'publication:home',
              params: { id: props.row.publication.id },
            }"
            >{{ props.row.publication.name }}
          </router-link>
        </q-td>
        <q-td key="status" :props="props">
          {{ $t(`submission.status.${props.row.status}`) }}
        </q-td>
        <q-td key="actions" :props="props">
          <q-btn flat icon="more_vert">
            <q-menu anchor="bottom right" self="top right">
              <q-item
                clickable
                :to="{
                  name: 'submission_details',
                  params: { id: props.row.id },
                }"
                ><q-item-section>Submission Details</q-item-section></q-item
              >
              <q-item clickable>
                <q-item-section data-cy="change_status_item_section">
                  <q-item-label> Change Status </q-item-label>
                </q-item-section>
              </q-item>
            </q-menu>
          </q-btn>
        </q-td>
      </q-tr>
    </template>
  </q-table>
</template>
<script setup>
defineProps({
  tableData: {
    type: Array,
    default: () => [],
  },
  tableTitle: {
    type: String,
    default: "",
  },
  tableByline: {
    type: String,
    default: "",
  },
  type: {
    type: String,
    default: "",
  },
})
const cols = [
  {
    name: "id",
    field: "id",
    label: "Number",
    sortable: true,
    style: "width: 95px",
    align: "right",
  },
  {
    name: "title",
    field: "title",
    label: "Submission Title",
    sortable: true,
    align: "left",
  },
  {
    name: "publication",
    field: "publication",
    label: "Publication Name",
    sortable: true,
    align: "left",
    style: "width: 10%",
  },
  {
    name: "status",
    field: "status",
    label: "Status",
    sortable: true,
    align: "center",
  },
  {
    name: "actions",
    field: "actions",
    label: "Actions",
    sortable: false,
    style: "width: 100px",
    align: "center",
  },
]
</script>
