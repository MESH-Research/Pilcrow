<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img
      src="avatar.png"
      alt="User Avatar"
    />
  </q-avatar>
</template>

<script>
export default {
  name: "AvatarImage"
};
</script>
