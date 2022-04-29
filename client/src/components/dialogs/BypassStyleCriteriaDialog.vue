<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row">
        <div class="q-pa-sm column">
          <q-avatar icon="toggle_on" color="primary" text-color="white" />
        </div>
        <div class="column justify-center q-pr-md">
          <p class="q-ml-sm q-mb-none">
            <i18n-t keypath="dialog.bypassStyleCriteria.line_1" tag="span">
              <template #link>
                <a
                  href="https://publicphilosophyjournal.org/overview/"
                  target="_blank"
                >
                  {{ $t("dialog.bypassStyleCriteria.line_1_linktext") }}
                </a>
              </template>
            </i18n-t>
          </p>
          <p class="q-ml-sm q-mb-none">
            {{ $t("dialog.bypassStyleCriteria.line_2") }}
          </p>
        </div>
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          data-cy="dirtyYesPostComment"
          :label="$t('dialog.bypassStyleCriteria.action_submit')"
          color="negative"
          flat
          @click="onDialogOK"
        />
        <q-btn
          data-cy="dirtyNoGoBack"
          :label="$t('dialog.bypassStyleCriteria.action_cancel')"
          color="positive"
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
