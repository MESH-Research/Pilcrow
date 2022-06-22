<template>
  <q-form @submit="save()">
    <q-select
      v-model="itemUnderEdit"
      filled
      :options="options"
      label="Choose Content Block"
    >
      <template #option="scope">
        <q-item v-bind="scope.itemProps">
          <q-item-section>
            <q-item-label>
              {{ $t(getI18nString(scope.opt, "label")) }}
            </q-item-label>
            <q-item-label caption>
              {{ $t(getI18nString(scope.opt, "description")) }}
            </q-item-label>
          </q-item-section>
        </q-item>
      </template>
      <template #selected>
        <div v-if="itemUnderEdit">
          {{ $t(getI18nString(itemUnderEdit, "label")) }}
        </div>
      </template>
    </q-select>
    <div v-if="itemUnderEdit">
      <q-banner class="bg-yellow-2 q-ma-md" rounded>
        <template #avatar>
          <q-icon name="tips_and_updates" />
        </template>
        <div class="text-h4">
          {{ $t(getI18nString(itemUnderEdit, "label")) }}
        </div>
        <div>
          {{ $t(getI18nString(itemUnderEdit, "description")) }}
        </div>
        <div>
          {{ $t(getI18nString(itemUnderEdit, "hint")) }}
        </div>
      </q-banner>
      <q-editor
        v-model="v$.content.$model"
        :toolbar="[
          [
            {
              label: $q.lang.editor.formatting,
              icon: $q.iconSet.editor.formatting,
              list: 'no-icons',
              options: ['p', 'h2', 'h3', 'h4', 'h5', 'h6'],
            },
          ],
          ['bold', 'italic', 'underline'],
          ['link', 'unordered', 'ordered', 'outdent', 'indent'],
          ['undo', 'redo'],
        ]"
      />
    </div>

    <form-actions @reset-click="resetForm" />
  </q-form>
</template>

<script setup>
import FormActions from "src/components/molecules/FormActions.vue"
import { isEqual } from "lodash"
import { computed, inject, reactive, ref, toRef, watch, watchEffect } from "vue"
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
const itemUnderEdit = ref(null)
const publication = toRef(props, "publication")

const options = ["home_page_content", "new_submission_content"]
const applyDefaults = (obj) => {
  return {
    content: obj[itemUnderEdit.value] ?? "",
    field: itemUnderEdit.value,
  }
}

const rules = {
  content: {
    maxLength: maxLength(4096),
  },
}

const original = computed(() => applyDefaults(publication.value))
const form = reactive(applyDefaults({}))
const v$ = useVuelidate(rules, form)

const { dirty, errorMessage, saved } = inject("formState")
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
watch(itemUnderEdit, () => {
  saved.value = false
})
function save() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    errorMessage.value = "Oops, check form above for errors"
  } else {
    emit("save", form)
  }
}

const getI18nString = (field, item) =>
  `publication.content.fields.${field}.${item}`
</script>
