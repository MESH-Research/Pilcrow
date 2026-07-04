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

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarBlock on User {
    name
    username
    ...avatarImage
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "../atoms/AvatarImage.vue"
import type { avatarBlockFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarBlockFragment
  avatarSize?: string
}
withDefaults(defineProps<Props>(), {
  avatarSize: ""
})
</script>
