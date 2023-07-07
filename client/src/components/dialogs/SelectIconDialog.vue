<template>
  <q-dialog ref="dialogRef" full-width @hide="onDialogHide">
    <q-card>
      <q-card-section class="column q-gutter-sm">
        <q-input
          v-model="filter"
          :label="$t('publications.style_criteria.fields.icon.search')"
          icon="search"
          filled
        />
        <q-icon-picker
          :model-value="props.icon"
          :icons="IconSet.icons"
          style="height: 300px"
          :filter="filter"
          @update:model-value="onSave"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import "@quasar/quasar-ui-qiconpicker/src/index.sass"
import IconSet from "@quasar/quasar-ui-qiconpicker/src/components/icon-set/material-icons"
import { QIconPicker } from "@quasar/quasar-ui-qiconpicker/dist/index.esm.js"
import { useDialogPluginComponent } from "quasar"
import { ref } from "vue"

const filter = ref("")
const props = defineProps({
  icon: {
    type: String,
    default: "",
    required: false,
  },
})

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK } = useDialogPluginComponent()

function onSave(icon) {
  onDialogOK(icon)
}
</script>
