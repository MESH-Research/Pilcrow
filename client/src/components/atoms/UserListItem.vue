<template>
  <q-item class="q-px-lg">
    <q-item-section top avatar>
      <avatar-image :user="user" rounded />
    </q-item-section>
    <q-item-section>
      <q-item-label v-if="user.name">
        {{ user.name }}
      </q-item-label>
      <q-item-label v-else>
        {{ user.username }}
      </q-item-label>
      <q-item-label caption lines="1">
        {{ user.email }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
import AvatarImage from "./AvatarImage.vue"
export default {
  name: "UserListItem",
  components: { AvatarImage },
  props: {
    index: {
      type: Number,
      default: null,
    },
    user: {
      type: Object,
      default: () => {},
    },
  },
}
</script>
