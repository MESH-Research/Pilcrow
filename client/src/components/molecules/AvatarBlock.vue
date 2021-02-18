<template>
  <div>
    <avatar-image :size="avatarSize" class="q-mb-sm" />
    <div class="text-weight-bold">{{ user.name }}</div>
    <div>@{{ user.username }}</div>
  </div>
</template>

<script>
import AvatarImage from "../atoms/AvatarImage.vue";
export default {
  components: { AvatarImage },
  name: "AvatarBlock",
  props: {
    user: {
      type: Object
    },
    avatarSize: {
      type: String,
      default: ""
    }
  }
};
</script>
