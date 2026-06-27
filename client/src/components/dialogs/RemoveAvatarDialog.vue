<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="$t('admin_avatar_reports.action_remove')"
    @hide="onDialogHide"
  >
    <q-card style="min-width: 320px; max-width: 480px; width: 90vw">
      <q-card-section>
        <div class="text-h6">
          {{ $t("admin_avatar_reports.action_remove") }}
        </div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <p class="text-body2">
          {{ $t("admin_avatar_reports.confirm_remove") }}
        </p>
        <p class="text-body2 text-negative" data-cy="confirm_remove_detail">
          {{ $t("admin_avatar_reports.confirm_remove_detail") }}
        </p>
        <q-checkbox
          v-model="block"
          :label="$t('admin_avatar_reports.confirm_block_label')"
          data-cy="avatar_report_block_checkbox"
        />
        <p class="text-caption text-grey-7 q-mt-xs">
          {{ $t("admin_avatar_reports.confirm_block_help") }}
        </p>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t('admin_avatar_reports.cancel')"
          color="primary"
          @click="onDialogCancel"
        />
        <q-btn
          :label="$t('admin_avatar_reports.action_remove')"
          color="negative"
          data-cy="avatar_report_confirm_remove"
          @click="confirm"
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

const block = ref(false)

function confirm() {
  onDialogOK({ blockFutureUploads: block.value })
}
</script>
