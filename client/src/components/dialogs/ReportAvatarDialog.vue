<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="$t('dialog.reportAvatar.aria_label')"
    @hide="onDialogHide"
  >
    <q-card style="min-width: 320px; max-width: 520px; width: 90vw">
      <q-card-section>
        <div class="text-h6">{{ $t("dialog.reportAvatar.title") }}</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <p class="text-body2">
          {{ $t("dialog.reportAvatar.description") }}
        </p>
        <q-input
          v-model="reason"
          type="textarea"
          :label="$t('dialog.reportAvatar.reason_label')"
          :placeholder="$t('dialog.reportAvatar.reason_placeholder')"
          autogrow
          counter
          maxlength="1000"
          data-cy="report_avatar_reason"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t('dialog.reportAvatar.cancel')"
          color="primary"
          data-cy="report_avatar_cancel"
          @click="onDialogCancel"
        />
        <q-btn
          :label="$t('dialog.reportAvatar.submit')"
          color="negative"
          data-cy="report_avatar_submit"
          @click="submit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { useDialogPluginComponent } from "quasar"

// eslint-disable-next-line vue/define-emits-declaration
defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const reason = ref("")

function submit() {
  onDialogOK({ reason: reason.value.trim() || null })
}
</script>
