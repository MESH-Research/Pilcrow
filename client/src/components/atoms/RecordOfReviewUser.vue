<template>
  <q-card bordered style="width: 250px">
    <q-card-section>
      <h3 class="text-h4 text-bold" style="line-height: 1.25">
        {{ user.display_label }}
      </h3>
      <dl>
        <template v-if="user.profile_metadata?.academic_profiles">
          <dt v-if="user.profile_metadata?.academic_profiles?.orcid_id">
            <span>ORCID iD</span>
          </dt>
          <dd v-if="user.profile_metadata?.academic_profiles?.orcid_id">
            <span>
              {{ user.profile_metadata?.academic_profiles.orcid_id }}
            </span>
          </dd>
        </template>
        <dt>
          <span>Role</span>
        </dt>
        <dd>
          <span>{{ role }}</span>
        </dd>
      </dl>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import type { User } from "src/graphql/generated/graphql"
interface Props {
  user: User
  role: string
}
defineProps<Props>()
</script>
