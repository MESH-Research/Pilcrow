<template>
  <q-card flat square class="bg-grey-1">
    <q-card-section>
      <div class="row items-start no-wrap">
        <div class="col">
          <div class="row items-center">
            <div style="width: 30px">
              <avatar-image :user="user" round class="fit" />
            </div>
            <div class="text-subtitle1 q-pl-sm">Egestas</div>
          </div>
          <div class="q-pl-sm">
            <small>
              <q-icon size="sm" name="subdirectory_arrow_right" />
              <div
                style="display: inline-block; height: 18px; width: 18px"
                class="q-mr-sm"
              >
                <avatar-image :user="user" round class="fit" />
              </div>
              <span>Reply to Magna Fringilla</span>
            </small>
          </div>
          <small> February 18th, 2021 at 6:35pm</small>
        </div>

        <div class="col-auto">
          <q-btn color="grey-7" round flat icon="more_vert">
            <q-menu cover auto-close>
              <q-list>
                <q-item clickable>
                  <q-item-section>Remove Card</q-item-section>
                </q-item>
                <q-item clickable>
                  <q-item-section>Send Feedback</q-item-section>
                </q-item>
                <q-item clickable>
                  <q-item-section>Share</q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </q-btn>
        </div>
      </div>
    </q-card-section>

    <q-card-section class="q-py-none">
      <p>
        Sagittis eu volutpat odio facilisis. Vitae congue eu consequat ac.
        Cursus sit amet dictum sit amet. Nibh tellus molestie nunc non blandit
        massa enim. Et tortor consequat id porta nibh venenatis. Dictum at
        tempor commodo ullamcorper. Placerat orci nulla pellentesque dignissim.
        Rhoncus dolor purus non enim praesent elementum facilisis.
      </p>
    </q-card-section>

    <q-card-actions class="q-pa-md">
      <q-btn bordered color="primary">Reply</q-btn>
    </q-card-actions>

    <q-separator />
  </q-card>
</template>
<script setup>
import AvatarImage from "./AvatarImage.vue"
defineProps({
  user: {
    type: Object,
    default: () => {},
  },
})
</script>
