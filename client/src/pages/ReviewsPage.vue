<template>
  <article>
    <h2 class="q-pl-lg">{{ $t(`reviews.page_title`) }}</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-7 col-sm-6 col-xs-12">
        <p>Reviews where you are assigned as a submitter</p>
        <q-table
          title="To Collaborate"
          :columns="cols"
          :rows="rows_none"
          row-key="name"
        >
          <template #header>
            <h3>Title</h3>
          </template>
        </q-table>
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>To Review</h3>
        <p>Reviews where you are assigned as a reviewer</p>
        <q-table
          title="To Review"
          :rows="rows"
          :columns="cols"
          row-key="name"
        />
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>To Coordinate</h3>
        <p>Reviews where you are assigned as a review coordinator</p>
        <q-table />
      </section>
    </div>
  </article>
</template>

<script setup>
const cols = [
  {
    name: "id",
    label: "Number",
    sortable: true,
  },
  {
    name: "submission",
    label: "Submission",
    sortable: true,
  },
]
const rows = [
  {
    id: 20,
    submission: "My Big Proposal",
  },
]
const rows_none = []
</script>
