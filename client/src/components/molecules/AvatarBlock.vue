<template>
  <div>
    <avatar-image :size="avatarSize" :user="user" class="q-mb-sm" />
    <div class="text-weight-bold" data-cy="avatar_name">
      {{ user.name }}
    </div>
    <div data-cy="avatar_username">@{{ user.username }}</div>
  </div>
</template>

<script>
import AvatarImage from "../atoms/AvatarImage.vue";
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
};
</script>
