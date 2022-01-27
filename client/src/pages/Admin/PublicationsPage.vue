<template>
  <div>
    <h2 class="q-pl-lg">Publications</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section
        class="col-md-5 col-sm-6 col-xs-12"
        data-cy="create_new_publication_form"
      >
        <h3>Create New Publication</h3>
        <q-form @submit="createPublication()">
          <q-input
            ref="nameInput"
            v-model="pubV$.name.$model"
            :error="pubV$.name.$error"
            outlined
            label="Enter Name"
            data-cy="new_publication_input"
            bottom-slots
          >
            <template #error>
              <ErrorFieldRenderer
                :errors="pubV$.name.$errors"
                prefix="publications.create.name"
                data-cy="name_field_error"
              />
            </template>
          </q-input>

          <q-btn
            ref="submitBtn"
            :disabled="is_submitting"
            class="bg-primary text-white q-mt-lg"
            type="submit"
          >
            Save
          </q-btn>
        </q-form>
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>All Publications</h3>
        <q-list
          v-if="publications.length !== 0"
          bordered
          separator
          data-cy="publications_list"
          class="scroll"
        >
          <q-item
            v-for="publication in publications"
            :key="publication.id"
            class="column"
            clickable
            @click="goToPublicationDetails(publication.id)"
          >
            <q-item-section>
              <q-item-label>
                {{ publication.name }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
        <div v-else data-cy="no_publications_message">
          No Publications Created
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import { CREATE_PUBLICATION } from "src/graphql/mutations"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { ref, reactive, watch } from "vue"
import { useRouter } from "vue-router"

const { push } = useRouter()

function goToPublicationDetails(publicationId) {
  push({
    name: "publication_details",
    params: { id: publicationId },
  })
}

const is_submitting = ref(false)
const tryCatchError = ref(false)

// TODO: This query is assuming only one page of results.
const { result: publicationsResult } = useQuery(GET_PUBLICATIONS, { page: 1 })
const publications = useResult(
  publicationsResult,
  [],
  (data) => data.publications.data
)

const $externalResults = reactive({ name: [] })
const newPublication = reactive({
  name: "",
})

const publicationRules = {
  name: {
    required,
    maxLength: maxLength(256),
  },
}

const pubV$ = useVuelidate(publicationRules, newPublication, {
  $externalResults,
})

watch(
  () => newPublication.name,
  () => {
    $externalResults.name = []
  }
)
function resetForm() {
  newPublication.name = ""
}

const { notify } = useQuasar()
const { t } = useI18n()
//TODO: Extract makeNotify function into a composables (also used in submissiondetails)
function makeNotify(color, icon, message) {
  notify({
    group: false,
    progress: true,
    color: color,
    icon: icon,
    message: t(message),
    attrs: {
      "data-cy": "create_publication_notify",
    },
    html: true,
  })
  is_submitting.value = false
}

const { mutate } = useMutation(CREATE_PUBLICATION)

async function createPublication() {
  tryCatchError.value = false
  pubV$.value.$touch()
  if (pubV$.value.$errors.length) {
    pubV$.value.$errors.forEach(({ $validator }) => {
      this.makeNotify("negative", "error", `publications.create.${$validator}`)
    })
    return false
  }

  is_submitting.value = true
  try {
    await mutate({ ...newPublication }, { refetchQueries: ["GetPublications"] })

    makeNotify("positive", "check_circle", "publications.create.success")
    resetForm()
  } catch (error) {
    error.graphQLErrors.forEach((gqlError) => {
      if (gqlError.extensions.category == "validation") {
        $externalResults.name.push(
          gqlError.extensions.validation["publication.name"]
        )
      }
    })
  } finally {
    is_submitting.value = false
  }
}
</script>
