<template>
  <q-item class="q-px-none">
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
          class="gt-xs"
          size="12px"
          color="negative"
          flat
          dense
          :title="$t('user.unconfirmed.title')"
          icon="warning_amber"
          data-cy="user_unconfirmed"
          @click="unconfirmedVisibility = !unconfirmedVisibility"
        >
          <q-tooltip
            v-model="unconfirmedVisibility"
            anchor="top middle"
            self="center middle"
            >{{ $t("user.unconfirmed.tooltip") }}</q-tooltip
          >
        </q-btn>
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

<script setup>
import AvatarImage from "./AvatarImage.vue"
import { ref } from "vue"
defineProps({
  user: {
    type: Object,
    default: () => {},
  },
  actions: {
    type: Array,
    required: false,
    default: () => [],
  },
})
defineEmits(["actionClick"])
const unconfirmedVisibility = ref(false)
</script>
