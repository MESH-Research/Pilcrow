<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="$t('dialog.avatarCrop.aria_label')"
    @hide="onDialogHide"
  >
    <q-card class="avatar-crop-card">
      <q-card-section>
        <div class="text-h6">{{ $t("dialog.avatarCrop.title") }}</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <div class="cropper-wrap">
          <cropper
            ref="cropperRef"
            :src="src"
            :stencil-props="stencilProps"
            image-restriction="fit-area"
            class="cropper"
          />
        </div>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          :label="$t('dialog.avatarCrop.cancel')"
          color="primary"
          data-cy="avatar_crop_cancel"
          @click="onDialogCancel"
        />
        <q-btn
          :label="$t('dialog.avatarCrop.save')"
          color="primary"
          data-cy="avatar_crop_save"
          @click="handleSave"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { useDialogPluginComponent } from "quasar"
import { Cropper } from "vue-advanced-cropper"
import "vue-advanced-cropper/dist/style.css"

interface Props {
  src: string
  outputSize?: number
  mimeType?: string
}
const props = withDefaults(defineProps<Props>(), {
  outputSize: 512,
  mimeType: "image/png"
})

// eslint-disable-next-line vue/define-emits-declaration
defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const stencilProps = {
  aspectRatio: 1,
  handlers: {},
  movable: true,
  resizable: true
}

interface CropperInstance {
  getResult: () => { canvas?: HTMLCanvasElement }
}
const cropperRef = ref<CropperInstance | null>(null)

function handleSave() {
  const result = cropperRef.value?.getResult()
  if (!result?.canvas) return

  // Resize to a fixed output size to cap upload size regardless of source.
  const target = document.createElement("canvas")
  target.width = props.outputSize
  target.height = props.outputSize
  const ctx = target.getContext("2d")
  if (!ctx) return
  ctx.drawImage(result.canvas, 0, 0, props.outputSize, props.outputSize)

  target.toBlob((blob) => {
    if (!blob) return
    const ext = props.mimeType === "image/jpeg" ? "jpg" : "png"
    const file = new File([blob], `avatar.${ext}`, { type: props.mimeType })
    onDialogOK({ file })
  }, props.mimeType)
}
</script>

<style scoped>
.avatar-crop-card {
  min-width: 320px;
  max-width: 540px;
  width: 90vw;
}
/* Checkerboard backdrop so transparent or dark source images stay visible. */
.cropper-wrap {
  background-color: #e0e0e0;
  background-image:
    linear-gradient(45deg, #bbb 25%, transparent 25%),
    linear-gradient(-45deg, #bbb 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #bbb 75%),
    linear-gradient(-45deg, transparent 75%, #bbb 75%);
  background-size: 16px 16px;
  background-position:
    0 0,
    0 8px,
    8px -8px,
    8px 0;
  max-height: 60vh;
  overflow: hidden;
}
.cropper {
  max-height: 60vh;
}
</style>
