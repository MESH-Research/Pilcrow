<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el
        :label="$t('header.publications')"
        :to="{ name: 'publication:index' }"
      />
      <q-breadcrumbs-el :label="publication?.name ?? ''" />
    </q-breadcrumbs>
  </nav>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-px-lg">
    <div class="row">
      <h2 class="col-sm-12" data-cy="publication_details_heading">
        {{ publication.name }}
        <q-btn
          v-if="isPublicationAdmin"
          data-cy="configure_button"
          icon="settings"
          class="float-right"
          :to="{ name: 'publication:setup:basic', param: { id: id } }"
          >Configure Publication</q-btn
        >
      </h2>
      <!--  eslint-disable vue/no-v-html -->
      <div
        data-cy="publication_home_content"
        class="content"
        v-html="publication.home_page_content"
      />
      <!--  eslint-enable vue/no-v-html -->
    </div>
  </article>
</template>

<script setup>
import { GET_PUBLICATION } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => {
  return result.value?.publication ?? null
})

const isPublicationAdmin = computed(() => {
  return publication.value?.effective_role === "publication_admin"
})
</script>
