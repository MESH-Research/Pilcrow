<template>
  <q-item class="q-px-lg">
    <q-item-section top avatar>
      <avatar-image :user="user" rounded />
    </q-item-section>
    <q-item-section>
      <q-item-label v-if="user.name">
        {{ user.name }}
      </q-item-label>
      <q-item-label v-else>
        {{ user.username }}
      </q-item-label>
      <q-item-label caption lines="1" class="text--grey">
        {{ user.email }}
      </q-item-label>
    </q-item-section>
    <q-item-section v-if="actions.length" side>
      <div class="text-grey-8 q-gutter-xs">
        <q-btn
          v-for="{ ariaLabel, icon, action, help, cyAttr } in actions"
          :key="icon"
          class="gt-xs"
          size="12px"
          flat
          dense
          round
          :title="help"
          :icon="icon"
          :data-cy="`${cyAttr}`"
          :aria-label="`${ariaLabel} ${user.name || user.username}`"
          @click="$emit('actionClick', { user, action })"
        />
      </div>
    </q-item-section>
  </q-item>
</template>

<script>
import AvatarImage from "./AvatarImage.vue"
export default {
  name: "UserListItem",
  components: { AvatarImage },
  props: {
    user: {
      type: Object,
      default: () => {},
    },
    actions: {
      type: Array,
      required: false,
      default: () => [],
    },
  },
  emits: ["actionClick"],
}
</script>
