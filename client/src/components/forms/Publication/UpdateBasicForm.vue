<template>
  <q-form @submit="save()">
    <v-q-wrap t-prefix="publication.basic.fields" @vqupdate="updateInput">
      <v-q-input :v="v$.name" />
    </v-q-wrap>
    <form-actions @reset-click="resetForm" />
  </q-form>
</template>

<script setup>
import VQWrap from "src/components/atoms/VQWrap.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import { pick, isEqual } from "lodash"
import { computed, inject, reactive, toRef, watchEffect } from "vue"
import { useDirtyGuard } from "src/use/forms"
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

const original = computed(() => applyDefaults(publication.value))
const form = reactive(applyDefaults({}))
const v$ = useVuelidate(rules, form)

const { dirty, errorMessage } = inject("formState")
watchEffect(() => {
  dirty.value = !isEqual(original.value, form)
})
useDirtyGuard(dirty)

function resetForm() {
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
