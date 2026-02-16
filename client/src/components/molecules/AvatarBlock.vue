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

<script setup lang="ts">
import AvatarImage from "../atoms/AvatarImage.vue"

interface Props {
  user: Record<string, unknown>
  avatarSize?: string
}
withDefaults(defineProps<Props>(), {
  avatarSize: ""
})
</script>
