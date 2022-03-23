<template>
  <q-item
    clickable
    :class="{ unread: !note.read_at }"
    data-cy="notification_list_item"
    class="q-pl-none"
    @click="handleClick(note.id)"
  >
    <q-badge v-if="!note.read_at" />
    <q-item-section side class="q-px-md">
      <q-icon :size="iconSize" :name="iconMapper(note.data.type)" />
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

<script setup>
import { computed } from "vue"
import { Screen } from "quasar"
import { flatten } from "flat"
import iconMapper from "src/mappers/notification_icons"
import TimeAgo from "javascript-time-ago"
import { useMutation } from "@vue/apollo-composable"
import { MARK_NOTIFICATION_READ } from "src/graphql/mutations"

const timeAgo = new TimeAgo("en-US")
/**
 * Q-Item based component for displaying notification items.
 *
 * @see https://v1.quasar.dev/vue-components/list-and-list-items#qitem-api
 */

const props = defineProps({
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
})

const { mutate: markNotificationRead } = useMutation(MARK_NOTIFICATION_READ, {
  refetchQueries: ["currentUserNotifications"],
})

const handleClick = async (notification_id) => {
  await markNotificationRead({
    notification_id: notification_id,
  })
}

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
  return flatten(props.note, { delimiter: "_" })
})

/**
 * Relative representation of the note's time property
 */
const relativeTime = computed(() => {
  const style = Screen.lt.md ? "mini-now" : "long"
  return timeAgo.format(Date.parse(props.note.created_at), style)
})
</script>
