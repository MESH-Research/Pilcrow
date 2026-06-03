<template>
  <q-card flat bordered class="rorr-user">
    <q-card-section class="rorr-user__inner">
      <h3 class="text-h4 text-bold rorr-user__name">
        {{ user.display_label }}
      </h3>
      <p class="rorr-user__role">{{ role }}</p>
      <dl
        v-if="user.profile_metadata?.academic_profiles?.orcid_id"
        class="rorr-user__dl"
      >
        <dt>
          {{
            $t(
              "account.profile.fields.profile_metadata.academic_profiles.orcid_id.label"
            )
          }}
        </dt>
        <dd>{{ user.profile_metadata.academic_profiles.orcid_id }}</dd>
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

<style lang="sass" scoped>
@import 'src/css/quasar.variables.sass'

.rorr-user
  width: 220px
  background: transparent
  color: $dark
  border: 1px solid $light-grey

.rorr-user__inner
  display: flex
  flex-direction: column
  align-items: center
  text-align: center
  padding: 1.25rem 1rem

.rorr-user__name
  font-size: 1rem
  margin: 0 0 0.25rem
  line-height: 1.25

.rorr-user__role
  font-size: 0.7rem
  letter-spacing: 0.1em
  text-transform: uppercase
  color: $primary
  font-weight: 600
  margin: 0 0 0.75rem

.rorr-user__dl
  margin: 0
  font-size: 0.8rem

  dt
    font-size: 0.65rem
    text-transform: uppercase
    letter-spacing: 0.08em
    color: $dark-6
    margin-top: 0.25rem

  dd
    margin: 0
</style>
