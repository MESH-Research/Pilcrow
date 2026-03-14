<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card class="q-dialog-plugin" style="min-width: 400px">
      <q-card-section>
        <div class="text-h6">{{ $t("tokens.create_dialog_title") }}</div>
      </q-card-section>

      <q-card-section>
        <q-input
          v-model="tokenName"
          :label="$t('tokens.token_name_label')"
          :placeholder="defaultName"
          :hint="$t('tokens.token_name_hint')"
          outlined
          autofocus
          data-cy="token_name_input"
          :rules="[
            (val) =>
              (val?.length ?? 0) <= 255 || $t('tokens.token_name_max_length')
          ]"
          @keyup.enter="onOKClick"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t('guiElements.form.cancel')"
          color="primary"
          data-cy="cancel_button"
          @click="onDialogCancel"
        />
        <q-btn
          flat
          :label="$t('tokens.create_button')"
          color="primary"
          data-cy="create_button"
          @click="onOKClick"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue"
import { useDialogPluginComponent } from "quasar"

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const defaultName = Math.random().toString(36).substring(2, 22)
const tokenName = ref("")

function onOKClick() {
  onDialogOK(tokenName.value || defaultName)
}
</script>
