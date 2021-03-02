<template>
  <div>
    <avatar-image
      :size="avatarSize"
      class="q-mb-sm"
    />
    <div class="text-weight-bold">
      <span data-cy="avatar_name">{{ user.name }}</span>
    </div>
    <div>
      <span data-cy="avatar_username">@{{ user.username }}</span>
    </div>
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
      required: true
    },
    avatarSize: {
      type: String,
      default: "",
    }
  }
};
</script>
