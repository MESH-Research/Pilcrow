<template>
  <q-dialog ref="dialogRef" full-width @hide="onDialogHide">
    <q-card>
      <q-card-section> </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { useDialogPluginComponent } from "quasar"

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide } = useDialogPluginComponent()
</script>
