<template>
  <article v-if="loading">Loading...</article>
  <article v-else class="q-pl-lg">
    <h2>Add Meta Form</h2>
    <q-form data-cy="create_meta_form" @submit="handleSubmit()">
      <q-field borderless>
        <q-input
          v-model="v$.form_name.$model"
          :error="v$.form_name.$error"
          outlined
          label="Form Name"
          data-cy="new_meta_form_name_input"
        />
      </q-field>
      <q-field borderless class="q-my-md">
        <q-input
          v-model="v$.formCaption.$model"
          :error="v$.formCaption.$error"
          outlined
          label="Caption"
          data-cy="new_meta_form_caption_input"
        />
      </q-field>
      <q-field borderless>
        <q-checkbox v-model="v$.isRequired.$model" label="Required" />
      </q-field>
      <q-btn
        class="accent text-white q-mt-lg"
        type="submit"
        label="Create"
        :disable="saving"
        :loading="saving"
      />

      <q-btn
        :to="{ name: 'publication:setup:metaForms' }"
        class="text-white q-mt-lg"
        type="button"
        label="Cancel"
      ></q-btn>
    </q-form>
  </article>
</template>

<script setup>
import { useMetaFormCreation } from "src/use/publicationMetaForm"

const { createMetaForm, v$, saving } = useMetaFormCreation()
async function handleSubmit() {
  try {
    await createMetaForm()
    // const mutationResult = await createMetaForm()
    push({
      name: "publication:setup:metaForms"
    })
  } catch (e) {
    console.error(e)
    newStatusMessage("failure", `Something went wrong: ${e}`)
  }
}
</script>
