<template>
  <section data-cy="create_new_publication_form">
    <q-form class="q-pa-sm" @submit="submit">
      <q-input
        ref="nameInput"
        v-model="pubV$.name.$model"
        :error="pubV$.name.$error ? true : null"
        outlined
        :label="$t('publication.basic.fields.name.label')"
        data-cy="new_publication_input"
        dense
      >
        <template #error>
          <ErrorFieldRenderer
            :errors="pubV$.name.$errors"
            prefix="publication.basic.fields.name.errors"
            data-cy="name_field_error"
          />
        </template>
      </q-input>
    </q-form>
  </section>
</template>

<script setup lang="ts">
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { CREATE_PUBLICATION } from "src/graphql/mutations"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { useMutation } from "@vue/apollo-composable"
import { useFeedbackMessages } from "src/use/guiElements"
import { reactive } from "vue"
import { useI18n } from "vue-i18n"
import { useExternalResultFromGraphQL } from "src/use/forms"

interface Emits {
  created: [publication: { id: string; name: string }]
}

const emit = defineEmits<Emits>()
const newPublication = reactive({
  name: ""
})

const publicationRules = {
  name: {
    required,
    maxLength: maxLength(256)
  }
}
const { mutate, error } = useMutation(CREATE_PUBLICATION, {
  refetchQueries: ["GetPublications"]
})

const pubV$ = useVuelidate(publicationRules, newPublication, {
  ...useExternalResultFromGraphQL(newPublication, error)
})

function resetForm() {
  newPublication.name = ""
  pubV$.value.$reset()
}

const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "create_publication_notify"
  }
})

async function submit() {
  pubV$.value.$touch()
  try {
    const publication = await mutate({ ...newPublication })
    newStatusMessage("success", t("publications.create.success"))
    resetForm()
    emit("created", publication.data.createPublication)
  } catch (error) {
    console.log(error)
    newStatusMessage("failure", t("publications.create.failure"))
  }
}

defineExpose({ submit })
</script>
