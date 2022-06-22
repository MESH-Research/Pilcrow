<template>
  <q-form @submit="save()">
    <v-q-wrap
      t-prefix="publication.basic.fields"
      class="q-gutter-md"
      @vqupdate="updateInput"
    >
      <q-editor
        v-model="v$.home_page_content.$model"
        label="Home Page Content"
        hint="Do these take hints"
      />
      <q-editor
        v-model="v$.new_submission_content.$model"
        label="Home Page Content"
        hint="Do these take hints"
      />
    </v-q-wrap>
    <form-actions @reset-click="resetForm" />
  </q-form>
</template>

<script setup>
import VQWrap from "src/components/atoms/VQWrap.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import { pick, isEqual } from "lodash"
import { computed, inject, reactive, toRef, watchEffect } from "vue"
import { useDirtyGuard } from "src/use/forms"
import { maxLength } from "@vuelidate/validators"
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
  const defaults = {
    new_submission_content: "",
    home_page_content: "",
  }
  return Object.assign(defaults, pick(obj ?? {}, Object.keys(defaults)))
}

const rules = {
  home_page_content: {
    maxLength: maxLength(4096),
  },
  new_submission_content: {
    maxLength: maxLength(4096),
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
