<template>
  <div class="q-px-lg q-pt-md">
    <div v-if="!publicationUser && !loading" class="text-grey-7 q-pa-md">
      {{ $t("publication.manage.user_detail.not_found") }}
    </div>

    <template v-else-if="publicationUser">
      <!-- Header card: avatar + identity + invited badge -->
      <q-card flat bordered class="q-mb-md">
        <q-card-section class="row items-center no-wrap q-gutter-md">
          <avatar-image
            v-if="publicationUser.user"
            :user="publicationUser.user"
            size="72px"
            rounded
          />
          <div class="col column q-gutter-xs">
            <div class="row items-center q-gutter-sm">
              <div class="text-h6">
                {{ displayName }}
              </div>
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
        </q-card-section>
      </q-card>

      <!-- Role breakdown -->
      <div class="row q-col-gutter-md q-mb-md">
        <div class="col-12 col-md-4">
          <q-card flat bordered class="full-height">
            <q-card-section>
              <div class="text-overline text-grey-7">
                {{ $t("publication.manage.user_detail.role.submitter") }}
              </div>
              <div class="text-h4 q-my-xs">
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
        </div>
        <div class="col-12 col-md-4">
          <q-card flat bordered class="full-height">
            <q-card-section>
              <div class="text-overline text-grey-7">
                {{ $t("publication.manage.user_detail.role.reviewer") }}
              </div>
              <div class="row items-baseline q-gutter-md q-my-xs">
                <div>
                  <div class="text-h4">
                    {{ publicationUser.as_reviewer_active_count }}
                  </div>
                  <div class="text-caption text-grey-7">
                    {{ $t("publication.manage.user_detail.phase.active") }}
                  </div>
                </div>
                <div>
                  <div class="text-h4">
                    {{ publicationUser.as_reviewer_completed_count }}
                  </div>
                  <div class="text-caption text-grey-7">
                    {{ $t("publication.manage.user_detail.phase.completed") }}
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
        <div class="col-12 col-md-4">
          <q-card flat bordered class="full-height">
            <q-card-section>
              <div class="text-overline text-grey-7">
                {{ $t("publication.manage.user_detail.role.coordinator") }}
              </div>
              <div class="row items-baseline q-gutter-md q-my-xs">
                <div>
                  <div class="text-h4">
                    {{ publicationUser.as_coordinator_active_count }}
                  </div>
                  <div class="text-caption text-grey-7">
                    {{ $t("publication.manage.user_detail.phase.active") }}
                  </div>
                </div>
                <div>
                  <div class="text-h4">
                    {{ publicationUser.as_coordinator_completed_count }}
                  </div>
                  <div class="text-caption text-grey-7">
                    {{ $t("publication.manage.user_detail.phase.completed") }}
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Submissions list -->
      <h3 class="q-mt-lg q-mb-sm" style="font-size: 1.125rem">
        {{ $t("publication.manage.user_detail.submissions_heading") }}
      </h3>
      <q-list v-if="publicationUser.submissions.length" bordered separator>
        <q-item
          v-for="sub in publicationUser.submissions"
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
  query GetPublicationUserDetail($publicationId: ID!, $userId: ID!) {
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
        as_reviewer_active_count
        as_reviewer_completed_count
        as_coordinator_active_count
        as_coordinator_completed_count
        submissions {
          id
          title
          status
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
import { GetPublicationUserDetailDocument } from "src/graphql/generated/graphql"
import { setCrumbLabel } from "src/use/breadcrumbs"

definePage({
  name: "manage:publication:user",
  props: true,
  meta: {
    crumb: {
      label: "User"
    }
  }
})

interface Props {
  id: string
  userId: string
}
const props = defineProps<Props>()

const { result, loading } = useQuery(GetPublicationUserDetailDocument, () => ({
  publicationId: props.id,
  userId: props.userId
}))

const publicationUser = computed(() => result.value?.publication?.user ?? null)

const displayName = computed(
  () =>
    publicationUser.value?.user?.name ??
    publicationUser.value?.user?.email ??
    ""
)

setCrumbLabel(
  "manage:publication:user",
  computed(() => displayName.value || undefined)
)
</script>
