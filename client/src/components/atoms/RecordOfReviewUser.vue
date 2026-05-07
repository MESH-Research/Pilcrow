<template>
  <q-card bordered style="width: 250px">
    <q-card-section>
      <h3 class="text-h4 text-bold" style="line-height: 1.25">
        {{ user.display_label }}
      </h3>
      <dl>
        <template v-if="user.profile_metadata?.academic_profiles">
          <dt v-if="user.profile_metadata?.academic_profiles?.orcid_id">
            <span>{{
              $t("account.profile.fields.profile_metadata.academic_profiles.orcid_id.label")
            }}</span>
          </dt>
          <dd v-if="user.profile_metadata?.academic_profiles?.orcid_id">
            <span>
              {{ user.profile_metadata?.academic_profiles.orcid_id }}
            </span>
          </dd>
        </template>
        <dt>
          <span>{{ $t("role.self", 1) }}</span>
        </dt>
        <dd>
          <span>{{ role }}</span>
        </dd>
      </dl>
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment recordOfReviewUser on User {
    id
    display_label
    profile_metadata {
      academic_profiles {
        orcid_id
      }
    }
  }
`)
</script>

<script setup lang="ts">
import type { recordOfReviewUserFragment } from "src/graphql/generated/graphql"
interface Props {
  user: recordOfReviewUserFragment
  role: string
}
defineProps<Props>()
</script>
