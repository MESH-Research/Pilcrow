<template>
  <q-btn
    flat
    padding="none"
    data-cy="dropdown_notificiations"
    class="notifications-dropdown"
    :aria-label="$t('header.notification_button')"
  >
    <q-icon name="notifications" />
    <q-badge floating color="light-blue-3" rounded />

    <q-menu id="notifications-menu">
      <div class="notifications-container">
        <q-list
          role="navigation"
          aria-label="Dropdown Navigation"
          style="width: 400px"
          bordered
          separator
          class="notifications-list"
        >
          <notification-list-item
            v-for="(item, index) in items"
            :key="index"
            :note="item"
            clickable
            :class="{ unread: !item.viewed }"
          />
        </q-list>
        <q-btn-group spread>
          <q-btn to="/feed">View More</q-btn>
          <q-btn>Dismiss All</q-btn>
        </q-btn-group>
      </div>
    </q-menu>
  </q-btn>
</template>

<script>
import { defineComponent } from "@vue/composition-api"
import { notificationItems } from "src/graphql/fillerData"
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"

/**
 * Notification Dropdown menu
 */
export default defineComponent({
  name: "NotificationPopup",
  components: { NotificationListItem },
  setup() {
    return { items: notificationItems }
  },
})
</script>

<style lang="sass">

#notifications-menu.q-menu
  display: flex
  overflow: hidden
  flex-flow: column
.notifications-container
  display: flex
  flex-flow: column
  overflow: hidden
  .q-list.notifications-list
    display: flex
    flex-flow: column wrap
    width: auto
    overflow: hidden
    .q-item
      min-height: 40px
      padding: 2px 16px 2px 0px
      margin-right: 10px
      width: 410px
</style>
