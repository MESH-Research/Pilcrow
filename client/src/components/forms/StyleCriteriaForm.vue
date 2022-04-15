<template>
  <q-item>
    <q-item-section avatar top>
      <q-btn :icon="v$.icon.$model" dense @click="editIcon">
        <q-tooltip> Click to change icon </q-tooltip>
      </q-btn>
    </q-item-section>
    <q-item-section class="column q-gutter-sm">
      <q-input
        v-model="v$.name.$model"
        outlined
        label="Criteria Name"
        :error="v$.name.$error"
      >
      </q-input>
      <q-editor
        v-model="v$.description.$model"
        :toolbar="[
          ['bold', 'italic', 'underline'],
          ['unordered', 'ordered', 'outdent', 'indent'],
          ['undo', 'redo'],
        ]"
        placeholder="Enter a description for this style criteria"
      />

      <div class="row q-gutter-sm justify-end">
        <q-btn icon="check" label="Save" />
        <q-btn icon="cancel" label="Cancel" @click="$emit('cancel')" />
      </div>
    </q-item-section>
  </q-item>
</template>

<script setup>
import IconFieldDialog from "src/components/forms/IconFieldDialog.vue"
import { useQuasar } from "quasar"
import { reactive, onMounted } from "vue"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"

const props = defineProps({
  criteria: {
    type: Object,
    default: () => ({}),
  },
})
defineEmits(["cancel", "save"])

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

onMounted(() => {
  Object.assign(state, props.criteria)
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
</script>

<style></style>
