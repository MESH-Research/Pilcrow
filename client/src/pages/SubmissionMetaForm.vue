<template>
  <article v-if="loading" class="q-pa-lg">
    <q-spinner color="primary" />
  </article>
  <div v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el :label="$t('header.publications')" />
        <q-breadcrumbs-el label="Label" />
        <q-breadcrumbs-el> Crumb </q-breadcrumbs-el>
      </q-breadcrumbs>
    </nav>
    <div class="row flex-center q-pa-md">
      <div class="col-lg-6 col-md-7 col-sm-9 col-xs-12">
        <article>{{ submission }}</article>
        <div v-for="entity in meta_form.meta_prompts" :key="entity.id">
          <q-input v-if="entity.type === 'INPUT'" :label="entity.label" />
          <q-select
            v-if="entity.type === 'SELECT'"
            :label="entity.label"
            :options="JSON.parse(entity.options).options"
          >
          </q-select>
          <q-checkbox v-if="entity.type === 'CHECKBOX'" :label="entity.label" />
          <q-input
            v-if="entity.type === 'TEXTAREA'"
            :label="entity.label"
            type="textarea"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useQuery } from "@vue/apollo-composable"

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  formId: {
    type: String,
    required: true
  }
})
const { result, loading } = useQuery(GET_SUBMISSION_META_FORMS, props)
const submission = computed(() => result.value?.submission)
const meta_form = computed(() => submission.value?.publication.meta_form)
</script>

<script lang="ts">
import { gql } from "graphql-tag"
const GET_SUBMISSION_META_FORMS = gql`
  query SubmissionMetaForms($id: ID!, $formId: ID!) {
    submission(id: $id) {
      id
      title
      publication {
        meta_form(id: $formId) {
          id
          name
          meta_prompts {
            id
            label
            type
            options
          }
        }
      }
    }
  }
`
</script>
