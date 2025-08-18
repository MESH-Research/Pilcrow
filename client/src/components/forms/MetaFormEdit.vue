<template>
  <article class="q-pl-lg">
    <h2>Edit Meta Form</h2>
    <q-form data-cy="create_meta_form" @submit="submitHandler()">
      <q-field borderless style="width: 50vw">
        <q-input
          v-model="v$.name.$model"
          :error="v$.name.$error"
          outlined
          class="full-width"
          label="Form Name"
          data-cy="new_meta_form_name_input"
        />
      </q-field>
      <q-field borderless style="width: 50vw">
        <q-input
          v-model="v$.caption.$model"
          :error="v$.caption.$error"
          outlined
          class="full-width"
          label="Caption"
          data-cy="new_meta_form_caption_input"
        />
      </q-field>
      <q-field borderless>
        <q-checkbox v-model="v$.required.$model" label="Required" />
      </q-field>
      <div class="q-gutter-md q-mt-lg q-mb-xl">
        <q-btn
          class="accent text-white"
          type="submit"
          label="Create"
          :disable="saving"
          :loading="saving"
        />
        <q-btn
          class="text-white"
          type="button"
          label="Cancel"
          :disable="saving"
          :loading="saving"
          @click="cancelHandler"
        ></q-btn>
      </div>
    </q-form>
  </article>
</template>

<script setup>
import { useMetaFormCreation } from "src/use/publicationMetaForm"
const { createMetaForm, v$, saving } = useMetaFormCreation()

const props = defineProps({
  publication: {
    type: Object,
    required: true
  }
})

function cancelHandler() {
  emit("cancel")
}

async function submitHandler() {
  try {
    const mutationResult = await createMetaForm(props.publication.id)
    console.log(mutationResult)
    emit("submit")
  } catch (e) {
    console.error(e)
  }
}

const emit = defineEmits(["cancel", "submit"])
</script>
