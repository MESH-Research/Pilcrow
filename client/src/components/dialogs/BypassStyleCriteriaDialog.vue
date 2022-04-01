<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row">
        <div class="q-pa-sm column">
          <q-avatar icon="toggle_on" color="primary" text-color="white" />
        </div>
        <div class="column justify-center q-pr-md">
          <p class="q-ml-sm q-mb-none">
            Style criteria are an important part of
            <abbr title="Formative Peer Review">FPR</abbr>.
          </p>
          <p class="q-ml-sm q-mb-none">
            Are you sure you do not want to associate one with this comment?
          </p>
        </div>
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          flat
          color="negative"
          label="Post comment as is"
          data-cy="dirtyYesPostComment"
          @click="onDialogOK"
        />
        <q-btn
          label="add style criteria"
          color="positive"
          data-cy="dirtyNoGoBack"
          @click="onDialogCancel"
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
