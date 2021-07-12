<template>
  <q-page padding>
    <h2>{{ submission.title }}</h2>
    <p> {{ id }} </p>
  </q-page>
</template>

<script>
import { GET_SUBMISSION } from "src/graphql/queries";

export default {
  components: {
  },
  props: {
    id: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      submission: {
        title: null,
        publication: null,
        users: null,
      },
    }
  },
  apollo: {
    submission: {
      query: GET_SUBMISSION,
      variables () {
        return {
         id: this.id,
        }
      }
    }
  },
}
</script>
