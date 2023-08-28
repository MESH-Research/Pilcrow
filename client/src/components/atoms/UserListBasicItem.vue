<template>
  <q-item
    clickable
    class="q-px-lg"
    @click="$emit('actionClick', { user, action })"
  >
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
      <q-item-label caption>
        {{ user.email }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script>
import AvatarImage from "./AvatarImage.vue"
export default {
  name: "UserListBasicItem",
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
    action: {
      type: String,
      required: false,
      default: "",
    },
  },
  emits: ["actionClick"],
}
</script>
