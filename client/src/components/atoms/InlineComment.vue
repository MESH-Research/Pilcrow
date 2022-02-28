<template>
  <q-card flat square class="bg-grey-1">
    <q-card-section>
      <div class="row items-start justify-between content-stretch no-wrap">
        <div class="col-grow">
          <div class="row items-center">
            <div style="width: 30px">
              <avatar-image :user="user" round class="fit" />
            </div>
            <div class="text-subtitle1 q-pl-sm">Magna Fringilla</div>
          </div>
          <div>
            <small>February 17th, 2021 at 6:35pm</small>
          </div>
        </div>
        <comment-actions />
      </div>
    </q-card-section>

    <q-card-section class="q-py-none">
      <p>
        At quis risus sed vulputate odio. Aliquam eleifend mi in nulla. Ornare
        arcu odio ut sem nulla pharetra diam sit amet. Nulla pharetra diam sit
        amet. Faucibus ornare suspendisse sed nisi lacus sed. Commodo quis
        imperdiet massa tincidunt nunc pulvinar sapien. Egestas tellus rutrum
        tellus pellentesque eu tincidunt tortor.
      </p>
    </q-card-section>

    <div class="q-px-sm">
      <q-chip size="16px" icon="bookmark"> Relevance </q-chip>
      <q-chip size="16px" icon="bookmark"> Accessibility </q-chip>
      <q-chip size="16px" icon="bookmark"> Coherence </q-chip>
    </div>

    <q-card-actions class="q-pa-md">
      <q-btn bordered color="primary">Reply</q-btn>
    </q-card-actions>

    <q-separator />
  </q-card>
</template>

<script setup>
import AvatarImage from "./AvatarImage.vue"
import CommentActions from "./CommentActions.vue"
defineProps({
  user: {
    type: Object,
    default: () => {},
  },
})
</script>
