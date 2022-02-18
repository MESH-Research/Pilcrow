<template>
  <div class="row items-center q-pa-sm">
    <avatar-image :size="avatarSize" :user="user" />
    <div class="q-pl-sm column justify-center">
      <div class="text-weight-bold" data-cy="avatar_name">
        {{ user.name }}
      </div>
      <div data-cy="avatar_username">@{{ user.username }}</div>
    </div>
  </div>
</template>

<script>
import AvatarImage from "../atoms/AvatarImage.vue"
export default {
  name: "AvatarBlock",
  components: { AvatarImage },
  props: {
    user: {
      type: Object,
      required: true,
    },
    avatarSize: {
      type: String,
      default: "",
    },
  },
}
</script>
