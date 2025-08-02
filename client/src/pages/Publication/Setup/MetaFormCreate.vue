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
    <q-field borderless>
      <q-checkbox v-model="form_required" label="Required" />
    </q-field>
    <q-btn class="accent text-white q-mt-md" type="submit" label="Create" />

    <q-btn
      :to="{ name: 'publication:setup:metaForms' }"
      class="text-white q-mt-md"
      type="button"
      label="Cancel"
    ></q-btn>
  </article>
</template>

<script setup>
import { ref } from "vue"
const form_name = ref("")
const form_required = ref(false)
</script>
