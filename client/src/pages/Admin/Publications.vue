<template>
  <div>
    <h2 class="q-pl-lg">
      Publications
    </h2>
    <div class="q-gutter-lg row q-pa-lg">
      <div class="col">
        <h3>Create New Publication</h3>
        <q-input
          outlined
          class="q-mb-lg"
          label="Enter Name"
        />
        <q-btn
          class="bg-primary text-white"
          type="submit"
        >
          Save
        </q-btn>
      </div>
      <div class="col">
        <h3>All Publications</h3>
        <ul>
          <li
            v-for="publication in publications.data"
            :key="publication.id"
          >
            {{ publication.name }}
          </li>
          <li v-if="publications.data.length == 0">
            No Publications Created
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import { GET_PUBLICATIONS } from 'src/graphql/queries'

export default {
  data() {
    return {
      publications: {
        data: null
      },
      current_page: 1
    }
  },
  apollo: {
    publications: {
      query: GET_PUBLICATIONS,
      variables () {
        return {
          page:this.current_page
        }
      }
    }
  },
}
</script>
