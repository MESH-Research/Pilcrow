<template>
  <q-dialog
    ref="dialog"
    @hide="onDialogHide"
  >
    <q-card>
      <q-card-section class="row items-center">
        <q-avatar
          icon="mdi-file-undo"
          color="primary"
          text-color="white"
        />
        <span class="q-ml-sm">Are you sure you want to discard your changes?</span>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Keep Changes"
          color="primary"
          @click="onCancelClick"
        />
        <q-btn
          flat
          label="Discard"
          color="negative"
          @click="onOKClick"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import dialog from '../mixins/dialog';
export default {
  // name: 'ComponentName',
  mixins: [dialog],
  data () {
    return {}
  }

}
</script>
