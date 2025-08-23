<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="mdi-file-undo" color="primary" text-color="white" />
        <span class="q-ml-sm">{{ $t(`dialog.discardChanges.body`) }}</span>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t(`dialog.discardChanges.keep_changes`)"
          color="primary"
          data-cy="dirtyKeepChanges"
          @click="onDialogCancel"
        />
        <q-btn
          flat
          :label="$t(`dialog.discardChanges.discard`)"
          color="negative"
          data-cy="dirtyDiscardChanges"
          @click="onDialogOK"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { useDialogPluginComponent } from "quasar"

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()
</script>
