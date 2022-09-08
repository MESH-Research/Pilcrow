<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el :label="$t('submissions.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <h2 class="q-pl-lg">{{ submission.title }}</h2>
    <section>
      <q-banner class="bg-grey-3">
        <div class="flex row items-center">
          <h3 class="q-ml-sm q-mr-md text-h4">
            {{ $t("submission.status.title") }}
          </h3>
          <q-separator vertical />
          <q-chip
            class="q-ml-md"
            icon="radio_button_checked"
            color="primary"
            text-color="white"
          >
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
        </div>
      </q-banner>
    </section>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-12">
        <q-btn
          data-cy="submission_review_btn"
          color="primary"
          size="lg"
          :label="$t('submissions.action.review.name')"
          :to="{
            name: 'submission_review',
            params: { id: props.id },
          }"
        />
      </section>
    </div>
    <div class="q-pa-lg column q-gutter-lg">
      <assigned-users
        data-cy="submitters_list"
        relationship="submitters"
        :container="submission"
      />
      <assigned-users
        relationship="review_coordinators"
        data-cy="coordinators_list"
        :container="submission"
        mutable
        :max-users="1"
      />
      <assigned-users
        data-cy="reviewers_list"
        relationship="reviewers"
        :container="submission"
        mutable
      />
    </div>
    <section class="q-pa-lg">
      <h3>Activity</h3>
      <p v-if="submission.audits.length == 0">No Activity</p>
      <ul>
        <li v-for="audit in submission.audits" :key="audit.id">
          <p>
            {{ audit.event }} by
            <router-link
              :to="{
                name: 'user_details',
                params: { id: audit.user.id },
              }"
              >{{ audit.user.name || audit.user.username }}
            </router-link>

            at {{ audit.created_at }}
          </p>
          <div
            v-if="
              audit.event == 'updated' &&
              JSON.parse(audit.old_values).status != null
            "
          >
            <p>
              from {{ JSON.parse(audit.old_values).status }} to
              {{ JSON.parse(audit.new_values).status }}
            </p>
            <p
              v-if="JSON.parse(audit.new_values).status_change_comment != null"
            >
              Comment: {{ JSON.parse(audit.new_values).status_change_comment }}
            </p>
          </div>
        </li>
      </ul>
    </section>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import AssignedUsers from "src/components/AssignedUsersComponent.vue"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_SUBMISSION, { id: props.id })
const submission = computed(() => {
  return result.value?.submission ?? null
})
</script>
