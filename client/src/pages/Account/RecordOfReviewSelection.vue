<template>
  <article data-cy="record_of_review" class="q-pa-lg">
    <section>
      <div v-if="subsLoading" class="q-pa-lg">
        {{ $t("loading") }}
      </div>
      <div v-else-if="all_submissions" class="col-12">
        <record-of-review-table
          v-model:selected="selected_reviews"
          :table-data="reviews"
          data-cy="record-of-review_table"
        />
        <div class="q-py-lg">
          <q-btn
            label="Get Record of Review"
            :icon="
              selected_reviews.length === 0 ? `do_not_disturb` : `chevron_right`
            "
            color="accent"
            :disabled="selected_reviews.length === 0"
            @click="showPreview = true"
          ></q-btn>
        </div>
      </div>
    </section>
  </article>
  <article v-if="showPreview" id="report" class="q-pa-lg">
    <record-of-review
      v-for="review in selected_reviews"
      :key="review['id']"
      :review="review as any"
    />
  </article>
</template>

<script setup lang="ts">
import { useQuery } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { compareDatesDesc } from "src/utils/dateSort"
import { GET_SUBMISSIONS } from "src/graphql/queries"
import { useRoute, useRouter } from "vue-router"
import RecordOfReviewTable from "src/components/atoms/RecordOfReviewTable.vue"
import RecordOfReview from "./RecordOfReview.vue"

const route = useRoute()
const router = useRouter()
const selected_reviews = ref([])

function buildQuery() {
  const query: Record<string, string> = {}
  query.reviews = `[${selected_reviews.value.map((r) => r["id"]).join(",")}]`
  return query
}

const showPreview = computed({
  get: () => route.query.preview === "1",
  set: (val) => {
    if (val) {
      router
        .push({
          query: { ...buildQuery(), preview: "1" },
          hash: "#report"
        })
        .then(() => {
          // get data
          console.log(selected_reviews.value)
        })
    } else if (route.query.preview) {
      router.back()
    }
  }
})

const { result: all_submissions_result, loading: subsLoading } = useQuery(
  GET_SUBMISSIONS,
  {
    page: 1
  }
)
const all_submissions = computed(() => {
  return all_submissions_result.value?.submissions.data ?? []
})
const reviews = computed(() => {
  const s = all_submissions.value
  return [...s].sort((a, b) => compareDatesDesc(a.created_at, b.created_at))
})
</script>
