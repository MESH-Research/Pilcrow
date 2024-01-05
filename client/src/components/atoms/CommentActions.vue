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

<script setup>
import { inject, computed } from "vue"
import { useCurrentUser } from "src/use/user"

const { currentUser } = useCurrentUser()

// import { useQuasar } from "quasar"
// const { dialog } = useQuasar()

const comment = inject("comment")

const emit = defineEmits(["quoteReplyTo", "modifyComment", "deleteComment"])

const createdByCurrentUser = computed(() => {
  return currentUser.value.id == comment.created_by.id
})

async function deleteHandler() {
  emit("deleteComment")
  // dialog({
  //   component: ConfirmStatusChangeDialog,
  //   componentProps: {
  //     action: action,
  //     submissionId: props.submission.id,
  //   },
  // })
}
</script>
