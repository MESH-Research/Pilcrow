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
            v-if="checkCommentCreatedBy == false"
            data-cy="modifyComment"
            clickable
            @click="$emit('modifyComment')"
          >
            <q-item-section>{{
              $t("submissions.comment.actions.modify")
            }}</q-item-section>
          </q-item>
          <q-item clickable>
            <q-item-section
              >{{ $t("submissions.comment.actions.delete") }}
            </q-item-section>
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

const comment = inject("comment")

defineEmits(["quoteReplyTo", "modifyComment"])

const checkCommentCreatedBy = computed(() => {
  const userToCheck = currentUser.value.id
  const commentCreatedBy = comment.created_by.id
  if (userToCheck == commentCreatedBy) {
    return false
  } else {
    return true
  }
})
</script>
