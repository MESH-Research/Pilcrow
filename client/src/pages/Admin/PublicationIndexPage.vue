<template>
  <div>
    <h2 class="q-pl-lg">Publication Administration</h2>
    <q-expansion-item
      label="Create New Publication"
      switch-toggle-side
      header-class="light-grey"
      data-cy="create_pub_button"
    >
      <CreateForm @created="publicationCreated" />
    </q-expansion-item>
    <section v-if="!loading" class="column q-gutter-md items-center">
      <q-list
        v-if="publications.length !== 0"
        bordered
        separator
        data-cy="publications_list"
        class="full-width"
      >
        <q-item v-for="publication in publications" :key="publication.id">
          <q-item-section class="q-pa-sm">
            <q-item-label>
              {{ publication.name }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn-group flat>
              <q-btn
                icon="visibility"
                class="dark-grey"
                :to="{
                  name: 'publication:home',
                  params: { id: publication.id },
                }"
                aria-label="View Publication Page"
              >
                <q-tooltip :delay="500">View Publication</q-tooltip>
              </q-btn>

              <q-btn-dropdown
                auto-close
                class="dark-grey"
                aria-label="Configure Publication"
              >
                <q-list>
                  <q-item :to="destRoute(publication.id, 'basic')">
                    <q-item-section avatar>
                      <q-icon class="dark-grey" name="tune" />
                    </q-item-section>
                    <q-item-section>
                      {{ $t(pageTitleKey("basic")) }}
                    </q-item-section>
                  </q-item>
                  <q-item :to="destRoute(publication.id, 'users')">
                    <q-item-section avatar>
                      <q-icon class="dark-grey" name="people" />
                    </q-item-section>
                    <q-item-section>
                      {{ $t(pageTitleKey("users")) }}
                    </q-item-section>
                  </q-item>
                  <q-item :to="destRoute(publication.id, 'criteria')">
                    <q-item-section avatar>
                      <q-icon class="dark-grey" name="card_membership" />
                    </q-item-section>
                    <q-item-section>
                      {{ $t(pageTitleKey("criteria")) }}
                    </q-item-section>
                  </q-item>
                  <q-item :to="destRoute(publication.id, 'content')">
                    <q-item-section avatar>
                      <q-icon class="dark-grey" name="toc" />
                    </q-item-section>
                    <q-item-section>
                      {{ $t(pageTitleKey("content")) }}
                    </q-item-section>
                  </q-item>
                </q-list>
                <template #label>
                  <q-icon name="settings" />
                  <q-tooltip :delay="500">Configure Publication</q-tooltip>
                </template>
              </q-btn-dropdown>
            </q-btn-group>
          </q-item-section>
        </q-item>
      </q-list>
      <q-pagination
        v-if="paginatorInfo"
        data-cy="publications_pagination"
        v-bind="binds"
        class="col"
        v-on="listeners"
      />
      <div v-else data-cy="no_publications_message">
        No Publications Created
      </div>
    </section>
  </div>
</template>

<script setup>
import { GET_PUBLICATIONS } from "src/graphql/queries"
import { usePagination } from "src/use/pagination"
import CreateForm from "src/components/forms/Publication/CreateForm.vue"
import { useRouter } from "vue-router"
const destRoute = (id, page) => ({
  name: `publication:setup:${page}`,
  params: { id },
})

const pageTitleKey = (page) => `publication.setup_pages.${page}`
const pubsPaginator = usePagination(GET_PUBLICATIONS)
const {
  binds,
  listeners,
  data: publications,
  paginatorInfo,
  query: { loading },
} = pubsPaginator

const { push } = useRouter()
function publicationCreated(publication) {
  push({
    name: "publication:setup:basic",
    params: { id: publication.id },
  })
}
</script>
