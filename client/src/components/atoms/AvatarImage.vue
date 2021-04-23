<style lang="css">
  .q-avatar::before {
    border-radius: 50%;
    bottom: 0;
    box-shadow: 0 0 0 0.05rem #777, 0 0 0 0.1rem #fff;
    content: "";
    display: block;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 1;
  }
  .q-avatar.rounded-borders::before {
    border-radius: 4px;
  }
  .q-avatar.q-avatar--square::before {
    border-radius: unset;
  }
</style>

<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img
      :src="avatarSrc"
      alt="User Avatar"
    />
  </q-avatar>
</template>

<script>

const stringToInt = (s) => {
  var hash = 0, i, chr;
  if (s.length === 0) return hash;
  for (i = 0; i < s.length; i++) {
    chr   = s.charCodeAt(i);
    hash  = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
}

export default {
  name: "AvatarImage",
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  computed: {
    avatarSrc() {
      const colors = [
        'blue', 'cyan', 'green', 'magenta', 'orange', 'pine', 'purple', 'red', 'yellow'
      ];
      if (!this.user.email) {
        return '';
      }
      const number = Math.abs(stringToInt(this.user.email)) % colors.length;

      return `avatar-${colors[number]}.png`;
    }
  },
};
</script>
