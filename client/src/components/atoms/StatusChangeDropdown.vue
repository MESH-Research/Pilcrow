<template>
  <q-btn-dropdown
    v-if="!statusChangingDisabledByRole && !statusChangingDisabledByState"
    :label="$t(`submission.toolbar.status_options`)"
    flat
    menu-anchor="bottom right"
    menu-self="top right"
    data-cy="status-dropdown"
  >
    <q-btn-group flat square class="column q-pa-sm" data-cy="decision_options">
      <q-btn
        v-for="(state, index) in nextStates[submissionRef.status]"
        :key="index"
        :data-cy="states[state].dataCy"
        :color="states[state].color"
        :label="$t(`submission.action.${states[state].action}`)"
        :class="states[state].class"
        @click="
          states[state].action ? confirmHandler(states[state].action) : () => {}
        "
      ></q-btn>
    </q-btn-group>
  </q-btn-dropdown>
</template>
<script setup>
import ConfirmStatusChangeDialog from "../dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"
import { useStatusChangeControls } from "src/use/guiElements.js"
import { toRef } from "vue"

const { dialog } = useQuasar()

const props = defineProps({
  submission: {
    type: Object,
    default: null,
  },
})

const submissionRef = toRef(props, "submission")
const { statusChangingDisabledByRole, statusChangingDisabledByState, states, nextStates } =
  useStatusChangeControls(submissionRef)

async function confirmHandler(action) {
  await new Promise((resolve) => {
    dirtyDialog(action)
      .onOk(function () {
        resolve(true)
      })
      .onCancel(function () {
        resolve(false)
      })
  })
  {
    return false
  }
}
function dirtyDialog(action) {
  return dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action: action,
      submissionId: props.submission.id,
    },
  })
}
</script>
