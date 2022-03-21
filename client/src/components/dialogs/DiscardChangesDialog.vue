<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="mdi-file-undo" color="primary" text-color="white" />
        <span class="q-ml-sm"
          >Are you sure you want to discard your changes?</span
        >
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Keep Changes"
          color="primary"
          data-cy="dirtyKeepChanges"
          @click="onDialogCancel"
        />
        <q-btn
          flat
          label="Discard"
          color="negative"
          data-cy="dirtyDiscardChanges"
          @click="onDialogOK"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { useDialogPluginComponent } from "quasar"

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()
</script>
