<template>
  <div>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="'Publications'"
          :to="{ name: 'publication:index' }"
        />
        <q-breadcrumbs-el
          :label="publicationName"
          :to="{ name: 'publication:home', params }"
        />
        <q-breadcrumbs-el label="Setup" />

        <q-breadcrumbs-el :label="route.meta.name" />
      </q-breadcrumbs>
    </nav>
    <div class="row justify-center items-start content-start q-pa-md">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div class="row">
          <q-card-section
            class="col-sm-12 col-xs-12 flex flex-center avatar-profile-block q-mt-none"
          >
            <div class="row">
              <h2 class="col-sm-12" data-cy="publication_details_heading">
                {{ publicationName }}
              </h2>
            </div>
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
            <collapse-menu :items="items" />
          </q-card-section>
        </div>
      </q-card>
      <q-card class="col-sm-9 col-xs-12 no-shadow outline no-border-radius">
        <router-view v-if="publication" :publication="publication" />
      </q-card>
    </div>
  </div>
</template>

<script setup>
import CollapseMenu from "src/components/molecules/CollapseMenu.vue"
import { useQuery } from "@vue/apollo-composable"
import { GET_PUBLICATION } from "src/graphql/queries"
import { computed } from "vue"
import { useRoute } from "vue-router"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => result.value?.publication ?? null)
const publicationName = computed(() => publication.value?.name ?? "")
const route = useRoute()
const params = { id: props.id }
const items = [
  {
    icon: "account_circle",
    label: "General Settings",
    url: {
      name: "publication:setup:basic",
      params,
    },
  },
  {
    icon: "contact_page",
    label: "Users",
    url: {
      name: "publication:setup:users",
      params,
    },
  },
  {
    icon: "contact_page",
    label: "Style Criteria",
    url: {
      name: "publication:setup:criteria",
      params,
    },
  },
  {
    icon: "contact_page",
    label: "Page Content",
    url: {
      name: "publication:setup:content",
      params,
    },
  },
]
</script>
