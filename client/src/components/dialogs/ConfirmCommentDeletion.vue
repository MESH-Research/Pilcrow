<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar icon="delete" color="primary" text-color="white" />
        <span class="q-ml-sm">{{ $t(`dialog.deleteComment.body`) }}</span>
      </q-card-section>
      <q-card-section>
        <blockquote>
          {{ props.comment }}
        </blockquote>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t(`dialog.deleteComment.cancel`)"
          color="primary"
          data-cy="dirtyCancel"
          @click="onDialogCancel"
        />
        <q-btn
          flat
          :label="$t(`dialog.deleteComment.delete`)"
          color="negative"
          data-cy="dirtyDelete"
          @click="onDialogOK"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { useDialogPluginComponent } from "quasar"

const props = defineProps({
  comment: {
    type: String,
    default: "",
    required: false,
  },
})

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()
</script>
