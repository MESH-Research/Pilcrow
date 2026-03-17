<template>
  <div v-if="user" class="column">
    <h3 class="sr-only">Publications</h3>

    <div class="column">
      <q-list v-if="user.publications.data.length" bordered separator>
        <q-item
          v-for="assignment in user.publications.data"
          :key="assignment.publication.id"
          flat
          bordered
          clickable
          :to="{
            name: 'publication_details',
            params: { id: assignment.publication.id }
          }"
        >
          <q-item-section>
            <q-item-label>
              {{ assignment.publication.name }}
            </q-item-label>
            <q-item-label>
              <q-chip size="sm">{{
                $t(`admin.users.details.roles.${assignment.role}`)
              }}</q-chip>
            </q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
      <div v-else class="text-grey-7 text-body2 q-pa-md">
        {{ $t("admin.users.details.no_publications") }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import gql from "graphql-tag"

interface Props {
  id: string
}
const props = defineProps<Props>()

const GET_USER_PUBLICATIONS = gql`
  query getUserPublications($id: ID) {
    user(id: $id) {
      id
      publications(first: 100) {
        data {
          role
          publication {
            id
            name
          }
        }
      }
    }
  }
`

const { result } = useQuery(GET_USER_PUBLICATIONS, { id: props.id })
const user = computed(() => result.value?.user)
</script>
