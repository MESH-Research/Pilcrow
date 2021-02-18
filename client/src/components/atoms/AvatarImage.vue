<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img src="https://cdn.quasar.dev/img/boy-avatar.png" />
  </q-avatar>
</template>

<script>
export default {
  name: "AvatarImage"
};
</script>
