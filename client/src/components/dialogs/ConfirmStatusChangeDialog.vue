<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar icon="priority_high" color="negative" text-color="white" />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            <i18n-t keypath="dialog.confirmStatusChange.line_1" tag="span">
            </i18n-t>
          </p>
        </div>
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          data-cy="dirtyYesPostComment"
          :label="$t('dialog.confirmStatusChange.action_submit')"
          color="primary"
          @click="onDialogOK"
        />
        <q-btn
          data-cy="dirtyNoGoBack"
          :label="$t('dialog.confirmStatusChange.action_cancel')"
          color="grey"
          flat
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
