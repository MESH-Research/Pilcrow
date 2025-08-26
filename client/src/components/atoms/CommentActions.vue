<template>
  <div class="col-auto">
    <q-btn
      data-cy="commentActions"
      color="dark-grey"
      dense
      round
      flat
      icon="more_vert"
      :aria-label="$t('submissions.comment.actions_btn_aria')"
    >
      <q-menu anchor="bottom right" self="top right" auto-close>
        <q-list>
          <q-item data-cy="quoteReply" clickable @click="$emit('quoteReplyTo')"
            ><q-item-section>{{
              $t("submissions.comment.actions.quote_reply")
            }}</q-item-section></q-item
          >
          <q-item
            v-if="createdByCurrentUser"
            data-cy="modifyComment"
            clickable
            @click="$emit('modifyComment')"
          >
            <q-item-section>{{
              $t("submissions.comment.actions.modify")
            }}</q-item-section>
          </q-item>
          <q-item
            v-if="createdByCurrentUser"
            data-cy="deleteComment"
            clickable
            @click="deleteHandler()"
          >
            <q-item-section>{{
              $t("submissions.comment.actions.delete")
            }}</q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>

<script setup lang="ts">
import { inject, computed } from "vue"
import { useCurrentUser } from "src/use/user"
import ConfirmCommentDeletion from "../dialogs/ConfirmCommentDeletion.vue"
import { useQuasar } from "quasar"
const { dialog } = useQuasar()

const { currentUser } = useCurrentUser()

const comment = inject("comment")
const submission = inject("submission")

const emit = defineEmits(["quoteReplyTo", "modifyComment", "deleteComment"])

const createdByCurrentUser = computed(() => {
  return currentUser.value.id == comment.created_by.id
})

function deleteHandler() {
  emit("deleteComment")
  dialog({
    component: ConfirmCommentDeletion,
    componentProps: {
      comment: comment,
      submissionId: submission.value.id
    }
  })
}
</script>
