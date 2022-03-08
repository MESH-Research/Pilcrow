<template>
  <div class="col-auto">
    <q-btn
      color="grey-7"
      round
      flat
      icon="more_vert"
      aria-label="$t('comment.actions_btn_aria')"
    >
      <q-menu cover auto-close>
        <q-list>
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
