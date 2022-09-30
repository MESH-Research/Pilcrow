<template>
  <div class="col-auto">
    <q-btn
      data-cy="commentActions"
      class="dark-grey"
      dense
      round
      flat
      icon="more_vert"
      :aria-label="$t('submissions.comment.actions_btn_aria')"
    >
      <q-menu anchor="bottom right" self="top right" auto-close>
        <q-list>
          <q-item data-cy="quoteReply" clickable @click="$emit('quoteReplyTo')"
            ><q-item-section>Quote Reply</q-item-section></q-item
          >
          <q-item clickable>
            <q-item-section>{{
              $t("submissions.comment.actions.modify")
            }}</q-item-section>
          </q-item>
          <q-item clickable>
            <q-item-section>Share</q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>
