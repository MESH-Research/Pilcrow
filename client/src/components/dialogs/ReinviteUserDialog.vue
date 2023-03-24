<template>
  <q-dialog ref="dialogRef" @hide="onDialogHide">
    <q-card>
      <q-card-section class="row no-wrap">
        <div class="q-pa-sm q-pr-md column">
          <q-avatar icon="forward_to_inbox" color="accent" text-color="white" />
        </div>
        <div class="column justify-center q-my-sm q-mb-none">
          <p class="q-mb-none">
            {{ $t(`dialog.reinviteUser.${role}`, { email: email }) }}
          </p>
        </div>
      </q-card-section>
      <q-separator />
      <q-card-section>
        <div class="column items-center">
          <p>{{ $t(`dialog.reinviteUser.optional_message`) }}</p>
        </div>
        <q-input
          v-model="comment"
          filled
          data-cy="reinvite_comment"
          :label="$t(`submissions.invite_user.message.placeholder`)"
          type="textarea"
        />
      </q-card-section>

      <q-card-actions align="around" class="q-pb-md">
        <q-btn
          data-cy="dirtyYesReinviteUser"
          label="Reinvite User"
          color="primary"
          @click="onDialogOK(reinviteUser())"
        />
        <q-btn
          data-cy="dirtyNoCancelReinviteUser"
          :label="$t('guiElements.form.cancel')"
          color="grey"
          flat
          @click="onDialogCancel"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { useDialogPluginComponent, useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import {
  REINVITE_REVIEWER,
  REINVITE_REVIEW_COORDINATOR,
} from "src/graphql/mutations"
import { computed, ref } from "vue"
// import { useI18n } from "vue-i18n"

// const { t } = useI18n()
const { notify } = useQuasar()

defineEmits([...useDialogPluginComponent.emits])

const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const props = defineProps({
  role: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    required: true,
  },
  submissionId: {
    type: String,
    required: true,
  },
})

const comment = ref(null)

const opts = { variables: { id: props.submissionId, email: props.email } }
const mutations = {
  reviewers: REINVITE_REVIEWER,
  review_coordinators: REINVITE_REVIEW_COORDINATOR,
}
const setMutationType = computed(() => {
  return mutations[props.role]
})
const { mutate } = useMutation(setMutationType, opts)

async function reinviteUser() {
  try {
    await mutate({
      message: comment.value,
    })
    notify({
      color: "positive",
      message: "Success",
      icon: "done",
      attrs: {
        "data-cy": "reinvite_notify",
      },
    })
  } catch (error) {
    notify({
      color: "negative",
      message: "Failure",
      icon: "error",
      attrs: {
        "data-cy": "reinvite_notify",
      },
    })
  }
}
</script>
