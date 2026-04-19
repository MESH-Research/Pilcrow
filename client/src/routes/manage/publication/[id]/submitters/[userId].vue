<template>
  <div class="q-px-lg q-pt-md">
    <div v-if="!publicationUser && !loading" class="text-grey-7 q-pa-md">
      {{ $t("publication.manage.user_detail.not_found") }}
    </div>

    <template v-else-if="publicationUser">
      <q-card flat bordered class="q-mb-md">
        <q-card-section class="row items-center no-wrap q-gutter-md">
          <avatar-image :user="publicationUser.user" size="72px" rounded />
          <div class="col column q-gutter-xs">
            <div class="row items-center q-gutter-sm">
              <div class="text-h6">{{ displayName }}</div>
              <q-badge
                v-if="publicationUser.user.staged"
                color="warning"
                text-color="dark"
                :aria-label="$t('publication.manage.user_detail.invited_aria')"
              >
                <q-icon name="schedule" size="xs" class="q-mr-xs" />
                {{ $t("publication.manage.user_detail.invited_badge") }}
              </q-badge>
            </div>
            <div
              v-if="publicationUser.user.username"
              class="text-caption text-grey-7"
            >
              {{ publicationUser.user.username }}
            </div>
            <div v-if="publicationUser.user.email" class="text-body2">
              <a
                :href="`mailto:${publicationUser.user.email}`"
                class="text-primary"
              >
                {{ publicationUser.user.email }}
              </a>
            </div>
          </div>
          <q-card class="bg-grey-2" flat>
            <q-card-section class="q-pa-md text-center">
              <div class="text-overline text-grey-7">
                {{ $t("publication.manage.user_detail.role.submitter") }}
              </div>
              <div class="text-h4">
                {{ publicationUser.as_submitter_count }}
              </div>
              <div class="text-caption text-grey-7">
                {{
                  $t("publication.manage.user_detail.submissions", {
                    n: publicationUser.as_submitter_count
                  })
                }}
              </div>
            </q-card-section>
          </q-card>
        </q-card-section>
      </q-card>

      <h3 class="q-mt-lg q-mb-sm" style="font-size: 1.125rem">
        {{ $t("publication.manage.user_detail.submissions_heading") }}
      </h3>
      <q-list v-if="publicationUser.submissions.data.length" bordered separator>
        <q-item
          v-for="sub in publicationUser.submissions.data"
          :key="sub.id"
          clickable
          :to="{ name: 'submission:details', params: { id: sub.id } }"
        >
          <q-item-section>
            <q-item-label>{{ sub.title }}</q-item-label>
            <q-item-label caption>
              {{ $t(`submission.status.${sub.status}`) }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
      <div v-else class="text-grey-7">
        {{ $t("publication.manage.user_detail.no_submissions") }}
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPublicationSubmitterDetail($publicationId: ID!, $userId: ID!) {
    publication(id: $publicationId) {
      id
      user(id: $userId) {
        id
        user {
          id
          name
          username
          email
          staged
          ...avatarImage
        }
        as_submitter_count
        submissions(first: 25, roles: [submitter]) {
          paginatorInfo {
            total
            currentPage
            lastPage
          }
          data {
            id
            title
            status
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { GetPublicationSubmitterDetailDocument } from "src/graphql/generated/graphql"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:submitter",
  props: true,
  meta: {
    crumb: [
      {
        label: "Submitters",
        to: { name: "manage:publication:submitters" }
      },
      { label: "Submitter" }
    ]
  }
})

interface Props {
  id: string
  userId: string
}
const props = defineProps<Props>()

const { result, loading } = useQuery(
  GetPublicationSubmitterDetailDocument,
  () => ({
    publicationId: props.id,
    userId: props.userId
  })
)

const publicationUser = computed(() => result.value?.publication?.user ?? null)

const displayName = computed(
  () =>
    publicationUser.value?.user?.name ??
    publicationUser.value?.user?.email ??
    ""
)

setCrumbLabel(
  "manage:publication:submitter",
  computed(() => displayName.value || undefined)
)
</script>
