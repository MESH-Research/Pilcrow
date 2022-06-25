<template>
  <q-form @submit="save()">
    <v-q-wrap
      t-prefix="publication.basic.fields"
      class="q-gutter-md"
      @vqupdate="updateInput"
    >
      <v-q-input data-cy="name_field" :v="v$.name" />
      <v-q-toggle-button
        data-cy="visibility_field"
        :v="v$.is_publicly_visible"
      />
    </v-q-wrap>
    <form-actions @reset-click="resetForm" />
  </q-form>
</template>

<script setup>
import VQWrap from "src/components/atoms/VQWrap.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQToggleButton from "src/components/atoms/VQToggleButton.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import { pick, isEqual } from "lodash"
import { computed, inject, reactive, toRef, watchEffect } from "vue"
import { useDirtyGuard, useExternalResultFromGraphQL } from "src/use/forms"
import { maxLength, required } from "@vuelidate/validators"
import useVuelidate from "@vuelidate/core"

const props = defineProps({
  publication: {
    required: true,
    validator: (v) => v === null || typeof v === "object",
  },
})
const emit = defineEmits(["save"])

const publication = toRef(props, "publication")

const applyDefaults = (obj) => {
  return Object.assign(
    { name: "", is_publicly_visible: false },
    pick(obj ?? {}, ["name", "is_publicly_visible"])
  )
}

const rules = {
  name: {
    required,
    maxLength: maxLength(256),
  },
  is_publicly_visible: {
    boolean: (value) => typeof value === "boolean",
  },
}
const form = reactive(applyDefaults({}))
const original = computed(() => applyDefaults(publication.value))

const { dirty, errorMessage, mutationError, reset } = inject("formState")
const { clearErrors: clearGraphQLErrors, $externalResults } =
  useExternalResultFromGraphQL(form, mutationError)
const v$ = useVuelidate(rules, form, {
  $externalResults,
})
watchEffect(() => {
  dirty.value = !isEqual(original.value, form)
})
useDirtyGuard(dirty)

function resetForm() {
  reset()
  clearGraphQLErrors()
  Object.assign(form, original.value)
}

watchEffect(() => {
  Object.assign(form, original.value)
})

function updateInput(validator, newValue) {
  validator.$model = newValue
}

function save() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    errorMessage.value = "Oops, check form above for errors"
  } else {
    emit("save", form)
  }
}
</script>
