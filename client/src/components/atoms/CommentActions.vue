<template>
  <div class="col-auto">
    <q-btn
      color="grey-7"
      dense
      round
      flat
      icon="more_vert"
      :aria-label="$t('submissions.comment.actions_btn_aria')"
    >
      <q-menu cover auto-close>
        <q-list>
          <q-item clickable @click="$emit('quoteReplyTo')"
            ><q-item-section>Quote Reply</q-item-section></q-item
          >
          <q-item clickable @click="$emit('replyTo')"
            ><q-item-section>Reply</q-item-section></q-item
          >
          <q-item clickable>
            <q-item-section>Modify</q-item-section>
          </q-item>
          <q-item clickable>
            <q-item-section>Share</q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>
