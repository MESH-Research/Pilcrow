<template>
  <div>
    <h2 class="q-pl-lg">
      Publications
    </h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section class="col-md-5 col-sm-6 col-xs-12">
        <h3>Create New Publication</h3>
        <q-form
          @submit="createPublication()"
        >
          <q-input
            v-model="new_publication.name"
            outlined
            class="q-mb-lg"
            label="Enter Name"
          />
          <q-btn
            :disabled="is_submitting"
            class="bg-primary text-white"
            type="submit"
          >
            Save
          </q-btn>
        </q-form>
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>All Publications</h3>
        <ol>
          <li
            v-for="publication in publications.data"
            :key="publication.id"
          >
            {{ publication.name }}
          </li>
        </ol>
        <span v-if="publications.data.length == 0">
          No Publications Created
        </span>
      </section>
    </div>
  </div>
</template>

<script>
import { GET_PUBLICATIONS } from 'src/graphql/queries';
import { CREATE_PUBLICATION } from 'src/graphql/mutations';

export default {
  data() {
    return {
      is_submitting: false,
      publications: {
        data: []
      },
      new_publication: {
        name: ""
      }
    }
  },
  apollo: {
    publications: {
      query: GET_PUBLICATIONS
    }
  },
  methods: {
    async createPublication() {
      this.is_submitting = true
      try {
        await this.$apollo.mutate({
          mutation: CREATE_PUBLICATION,
          variables: this.new_publication,
        })
        this.$q.notify({
          color: "positive",
          message: this.$t("publications.create.success"),
          icon: "check_circle",
          attrs: {
            'data-cy': 'create_publication_notify'
          },
          html: true
        });
        this.is_submitting = false;
        this.new_publication.name = "";
      } catch (error) {
        this.$q.notify({
          color: "negative",
          message: this.$t("publications.create.failure"),
          icon: "error",
          attrs: {
            'data-cy': 'create_publication_notify'
          },
          html: true
        });
        this.is_submitting = false
      }
    }
  },
}
</script>
