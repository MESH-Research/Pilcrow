<template>
  <div>
    <avatar-image
      :size="avatarSize"
      class="q-mb-sm"
    />
    <div class="text-weight-bold">
      {{ user.name }}
    </div>
    <div>@{{ user.username }}</div>
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
