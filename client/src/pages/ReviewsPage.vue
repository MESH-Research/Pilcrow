<template>
  <article>
    <h2 class="q-pl-lg">{{ $t(`reviews.page_title`) }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-10 col-sm-11 col-xs-12">
        <q-table
          bordered
          flat
          :columns="cols"
          :rows="reviewer_reviews"
          row-key="id"
          dense
        >
          <template #top>
            <div>
              <h3 class="q-my-none">To Review</h3>
              <p>
                Reviews in which you're assigned as a <strong>reviewer</strong>
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
                      ><q-item-section
                        >Submission Details</q-item-section
                      ></q-item
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
      </section>
      <section class="col-md-10 col-sm-11 col-xs-12 q-mt-lg">
        <q-table
          v-if="coordinator_reviews.length > 0"
          bordered
          flat
          :columns="cols"
          :rows="coordinator_reviews"
          row-key="id"
          dense
        >
          <template #top>
            <div>
              <h3 class="q-my-none">To Coordinate</h3>
              <p>
                Reviews in which you're assigned as a
                <strong>review coordinator</strong>
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
                      ><q-item-section
                        >Submission Details</q-item-section
                      ></q-item
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
      </section>
    </div>
  </article>
</template>

<script setup>
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
const { result } = useQuery(CURRENT_USER_SUBMISSIONS)
const submissions = computed(() => {
  return result.value?.currentUser?.submissions ?? []
})
const reviewer_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      submission.status != "DRAFT" &&
      submission.status != "INITIALLY_SUBMITTED" &&
      submission.my_role == "reviewer"
    )
  })
)
const coordinator_reviews = computed(() =>
  submissions.value.filter(function (submission) {
    return (
      submission.status != "DRAFT" &&
      submission.status != "INITIALLY_SUBMITTED" &&
      submission.my_role == "review_coordinator"
    )
  })
)
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
