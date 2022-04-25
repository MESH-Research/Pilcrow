<template>
  <q-form @submit="onSave">
    <q-separator />
    <q-item>
      <q-item-section avatar top>
        <q-btn :icon="v$.icon.$model" dense @click="editIcon">
          <q-tooltip> Click to change icon </q-tooltip>
        </q-btn>
      </q-item-section>
      <q-item-section class="column q-gutter-sm">
        <v-q-input
          :v="v$.name"
          label="Criteria Name"
          t="publications.style_criteria.fields.name"
          @vqupdate="updateModel"
        />
        <q-editor
          v-model="v$.description.$model"
          :toolbar="[
            ['bold', 'italic', 'underline'],
            ['unordered', 'ordered', 'outdent', 'indent'],
            ['undo', 'redo'],
          ]"
          :class="v$.description.$error ? 'error' : ''"
          placeholder="Enter a description for this style criteria"
        />
        <div
          v-if="v$.description.$error && v$.description.maxLength.$invalid"
          class="text-negative"
        >
          {{
            $t(
              "publications.style_criteria.fields.description.errors.maxLength"
            )
          }}
        </div>

        <div class="row justify-end">
          <FormActions :sticky="false" @reset-click="onCancel" />
        </div>
      </q-item-section>
    </q-item>
    <q-separator />
  </q-form>
</template>

<script setup>
import IconFieldDialog from "src/components/forms/IconFieldDialog.vue"
import { useQuasar } from "quasar"
import { reactive, onMounted, inject, watchEffect, computed } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"
import { isEqual, pick } from "lodash"
import VQInput from "src/components/atoms/VQInput.vue"
import FormActions from "../molecules/FormActions.vue"

const { dirty, setError } = inject("formState")

const props = defineProps({
  criteria: {
    type: Object,
    default: () => ({}),
  },
})
const emit = defineEmits(["cancel", "save"])

const state = reactive({
  id: "",
  name: "",
  icon: "task_alt",
  description: "",
})

const rules = {
  name: {
    required,
    maxLength: maxLength(20),
  },
  description: {
    maxLength: maxLength(4096),
  },
  icon: {
    maxLength: maxLength(50),
  },
}

const v$ = useVuelidate(rules, state)

const original = computed(() => ({
  ...pick(props.criteria, ["id", "name", "icon", "description"]),
}))

onMounted(() => {
  Object.assign(state, props.criteria)
})

watchEffect(() => {
  dirty.value = !isEqual(state, original.value)
})

const { dialog } = useQuasar()

function editIcon() {
  dialog({
    component: IconFieldDialog,
    componentProps: {
      icon: state.icon,
    },
  }).onOk((icon) => {
    v$.value.icon.$model = icon
  })
}

function onCancel() {
  emit("cancel")
}

function onSave() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    setError("Oops, check form above for errors")
  } else {
    emit("save", state)
  }
}

function updateModel(validator, value) {
  validator.$model = value
}
</script>

<style lang="sass">
.q-editor.error
  border: $negative 1px solid
</style>
