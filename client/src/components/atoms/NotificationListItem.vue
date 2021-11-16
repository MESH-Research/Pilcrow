<template>
  <q-item clickable :class="{ unread: !note.viewed }" class="q-pl-none">
    <q-badge v-if="!note.viewed" />

    <q-item-section side class="q-px-md">
      <q-icon :size="iconSize" :name="note.icon" />
    </q-item-section>
    <q-item-section>
      <p class="q-pa-none q-ma-none">
        {{ $t(`notifications.${note.type}.short`, flatten(note)) }}
      </p>
    </q-item-section>
    <q-item-section v-if="!!showTime" class="items-end">
      {{ DateTime.fromMillis(note.time).toRelative() }}
    </q-item-section>
  </q-item>
</template>

<script>
import { flatten } from "flat"
import { DateTime } from "luxon"
export default {
  props: {
    note: {
      type: Object,
      default: () => {},
    },
    showTime: {
      type: Boolean,
      default: false,
    },
    iconSize: {
      type: String,
      default: "xs",
    },
  },
  setup() {
    return { flatten, DateTime }
  },
}
</script>
