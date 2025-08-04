<template>
  <article class="q-px-md">
    <h2>Add Meta Form</h2>
    <q-field borderless>
      <q-input
        v-model="form_name"
        outlined
        label="Form Name"
        data-cy="new_meta_form_name_input"
      />
    </q-field>
    <q-field borderless class="q-my-md">
      <q-input
        v-model="form_caption"
        outlined
        label="Caption"
        data-cy="new_meta_form_caption_input"
      />
    </q-field>
    <q-field borderless>
      <q-checkbox v-model="form_required" label="Required" />
    </q-field>
    <q-btn class="accent text-white q-mt-lg" type="submit" label="Create" />

    <q-btn
      :to="{ name: 'publication:setup:metaForms' }"
      class="text-white q-mt-lg"
      type="button"
      label="Cancel"
    ></q-btn>
  </article>
</template>

<script setup>
import { ref } from "vue"
const form_name = ref("")
const form_caption = ref("")
const form_required = ref(false)
</script>
