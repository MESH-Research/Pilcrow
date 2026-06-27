<template>
  <div
    v-if="user.can_upload_avatar"
    class="avatar-uploader q-gutter-md items-center"
  >
    <div class="row items-center q-gutter-md">
      <avatar-image
        :user="user"
        variant="medium"
        rounded
        class="avatar-preview"
      />
      <div class="column q-gutter-sm">
        <div class="text-weight-bold">{{ $t("account.avatar.heading") }}</div>
        <div class="text-caption text-grey-8">
          {{ $t("account.avatar.description") }}
        </div>
        <div class="text-caption text-grey-8">
          {{ $t("account.avatar.acceptable_use") }}
        </div>
        <div class="row q-gutter-sm">
          <q-btn
            :label="
              user.avatar
                ? $t('account.avatar.change_button')
                : $t('account.avatar.upload_button')
            "
            color="primary"
            :loading="uploading"
            data-cy="avatar_upload_button"
            @click="pickFile"
          />
          <q-btn
            v-if="user.avatar"
            flat
            :label="$t('account.avatar.remove_button')"
            color="negative"
            :loading="removing"
            data-cy="avatar_remove_button"
            @click="remove"
          />
        </div>
      </div>
    </div>
    <input
      ref="fileInput"
      type="file"
      accept="image/jpeg,image/png,image/webp"
      class="hidden-input"
      data-cy="avatar_file_input"
      @change="onFileSelected"
    />
  </div>
  <div
    v-else-if="user.avatar_upload_blocked"
    class="avatar-uploader-blocked text-caption text-grey-8"
    data-cy="avatar_upload_blocked_notice"
  >
    {{ $t("account.avatar.upload_blocked_by_moderator") }}
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue"
import { Dialog, Notify } from "quasar"
import { useI18n } from "vue-i18n"
import { useMutation } from "@vue/apollo-composable"
import AvatarImage from "../atoms/AvatarImage.vue"
import AvatarCropDialog from "../dialogs/AvatarCropDialog.vue"
import { UPLOAD_USER_AVATAR, DELETE_USER_AVATAR } from "src/graphql/mutations"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarImageFragment & {
    can_upload_avatar?: boolean
    avatar_upload_blocked?: boolean
  }
}
const props = defineProps<Props>()

const { t } = useI18n()

const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"]
const MAX_SIZE_BYTES = 5 * 1024 * 1024

const fileInput = ref<HTMLInputElement | null>(null)
const uploading = ref(false)
const removing = ref(false)

const { mutate: uploadAvatar } = useMutation(UPLOAD_USER_AVATAR, {
  context: { hasUpload: true }
})
const { mutate: deleteAvatar } = useMutation(DELETE_USER_AVATAR)

function pickFile() {
  fileInput.value?.click()
}

function validateFile(file: File): boolean {
  if (!ACCEPTED_TYPES.includes(file.type)) {
    Notify.create({
      type: "negative",
      message: t("account.avatar.invalid_type")
    })
    return false
  }
  if (file.size > MAX_SIZE_BYTES) {
    Notify.create({ type: "negative", message: t("account.avatar.too_large") })
    return false
  }
  return true
}

function onFileSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  input.value = "" // allow re-selecting the same file later
  if (!file || !validateFile(file)) return

  const reader = new FileReader()
  reader.onload = () => {
    openCropper(String(reader.result))
  }
  reader.readAsDataURL(file)
}

function openCropper(src: string) {
  Dialog.create({
    component: AvatarCropDialog,
    componentProps: { src, outputSize: 512, mimeType: "image/png" }
  }).onOk(({ file }: { file: File }) => {
    void upload(file)
  })
}

async function upload(file: File) {
  uploading.value = true
  try {
    await uploadAvatar({ id: props.user.id, avatar: file })
    Notify.create({
      type: "positive",
      message: t("account.avatar.upload_success")
    })
  } catch {
    Notify.create({
      type: "negative",
      message: t("account.avatar.upload_failure")
    })
  } finally {
    uploading.value = false
  }
}

async function remove() {
  removing.value = true
  try {
    await deleteAvatar({ id: props.user.id })
    Notify.create({
      type: "positive",
      message: t("account.avatar.remove_success")
    })
  } catch {
    Notify.create({
      type: "negative",
      message: t("account.avatar.remove_failure")
    })
  } finally {
    removing.value = false
  }
}
</script>

<style scoped>
.avatar-preview {
  width: 96px;
  height: 96px;
}
.hidden-input {
  display: none;
}
</style>
