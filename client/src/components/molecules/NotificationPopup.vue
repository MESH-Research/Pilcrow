<template>
  <q-btn
    flat
    padding="none"
    data-cy="dropdown_notificiations"
    :aria-label="$t('header.notification_button')"
    aria-haspopup="true"
    :aria-expanded="isVisible ? 'true' : 'false'"
  >
    <q-icon name="notifications" />
    <q-badge
      v-if="hasUnreadNotifications"
      ref="notification_indicator"
      data-cy="notification_indicator"
      role="presentation"
      floating
      color="light-blue-3"
      rounded
    />

    <q-popup-proxy
      ref="popupProxy"
      v-model="isVisible"
      max-width="400px"
      position="top"
    >
      <div class="notifications-container">
        <q-list
          role="navigation"
          aria-label="Dropdown Navigation"
          bordered
          separator
          class="notifications-list"
        >
          <notification-list-item
            v-for="(item, index) in notificationItems"
            :key="index"
            :note="item"
            clickable
            :class="{ unread: !item.read_at }"
          />
        </q-list>
        <q-btn-group spread>
          <q-btn to="/feed">View More</q-btn>
          <q-btn data-cy="dismiss_all_notifications" @click="dismissAll"
            >Dismiss All</q-btn
          >
        </q-btn-group>
      </div>
    </q-popup-proxy>
  </q-btn>
</template>

<script setup>
import { ref, watch, nextTick } from "vue"
import { useQuery, useResult } from "@vue/apollo-composable"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"
import { computed } from "vue"
import { MARK_ALL_NOTIFICATIONS_READ } from "src/graphql/mutations"

const currentPage = ref(1)
const popupProxy = ref(null)
const isVisible = ref(false)
const { result } = useQuery(CURRENT_USER_NOTIFICATIONS, {
  page: currentPage,
})
const notificationItems = useResult(
  result,
  [],
  (data) => data.currentUser.notifications.data
)
const hasUnreadNotifications = computed(() => {
  return notificationItems.value.length > 0 &&
    notificationItems.value.find((item) => item.data.read_at === null)
    ? true
    : false
})
watch(isVisible, (newValue) => {
  if (newValue === false) {
    return
  }
  nextTick(() => {
    popupProxy.value.$refs.popup.$children[0].$el.id = "notifications-wrapper"
  })
})
const { mutate: markAllNotificationsRead } = useMutation(
  MARK_ALL_NOTIFICATIONS_READ,
  {
    refetchQueries: ["currentUserNotifications"],
  }
)
const dismissAll = async () => {
  await markAllNotificationsRead()
}
</script>

<style lang="sass">
#notifications-wrapper.q-menu
  display: flex
.notifications-container
  background: white
  display: flex
  flex-flow: column
  overflow: hidden
  .q-list.notifications-list
    display: flex
    flex-flow: column wrap
    overflow: hidden
    .q-item
      min-height: 40px
      padding: 4px 16px 4px 0px
      width: 100%
      margin-right: 10px
</style>
