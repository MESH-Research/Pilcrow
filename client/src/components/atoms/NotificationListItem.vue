<template>
  <q-item clickable :class="{ unread: !note.data.read_at }" class="q-pl-none">
    <q-badge v-if="!note.data.read_at" />
    <q-item-section side class="q-px-md">
      <q-icon :size="iconSize" :name="note.icon" />
    </q-item-section>
    <q-item-section>
      <p class="q-pa-none q-ma-none">
        {{ $t(tKey, flattened) }}
      </p>
    </q-item-section>
    <q-item-section
      v-if="!!showTime"
      class="text-caption items-end col-md-3 col-xs-1"
    >
      {{ relativeTime }}
    </q-item-section>
  </q-item>
</template>

<script>
import { computed } from "@vue/composition-api"
import { Screen } from "quasar"
import { flatten } from "flat"
import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"

TimeAgo.addDefaultLocale(en)

const timeAgo = new TimeAgo("en-US")
/**
 * Q-Item based component for displaying notification items.
 *
 * @see https://v1.quasar.dev/vue-components/list-and-list-items#qitem-api
 */
export default {
  props: {
    /**
     * Notification item to display
     */
    note: {
      type: Object,
      requred: true,
      default: () => {},
    },
    /**
     * Show the relative time q-item-section
     */
    showTime: {
      type: Boolean,
      default: false,
    },
    /**
     * Specify the size for the q-icon
     * @see https://v1.quasar.dev/vue-components/icon#size-and-colors
     */
    iconSize: {
      type: String,
      default: "sm",
    },
  },
  setup(props) {
    /**
     * The parsed translation key to use for the supplied note
     */
    const tKey = computed(() => {
      return `notifications.${props.note.data.type}.short`
    })
    /**
     * Flattened version of the note for passing to i18n
     */
    const flattened = computed(() => {
      return flatten(props.note.data)
    })
    /**
     * Relative representation of the note's time property
     */
    const relativeTime = computed(() => {
      const style = Screen.lt.md ? "mini-now" : "long"
      return timeAgo.format(new Date(props.note.data.time), style)
    })
    return { tKey, flattened, relativeTime }
  },
}
</script>
