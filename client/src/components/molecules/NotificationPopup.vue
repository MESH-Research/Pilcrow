<template>
  <q-btn
    flat
    padding="none"
    data-cy="dropdown_notificiations"
    class="notifications-dropdown"
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
          <q-item
            v-for="(item, index) in items"
            :key="index"
            clickable
            :class="{ unread: !item.viewed }"
          >
            <q-badge v-if="!item.viewed" />

            <q-item-section side="left" class="q-pr-xs q-pl-sm">
              <q-icon size="xs" :name="item.icon" />
            </q-item-section>
            <q-item-section>
              <p class="q-pa-none q-ma-none">
                <span href="/">{{ item.user }}</span> {{ item.text }}
                <span href="#">{{ item.object }}</span>
              </p>
            </q-item-section>
          </q-item>
        </q-list>
        <q-btn-group spread>
          <q-btn>View More</q-btn>
          <q-btn>Dismiss All</q-btn>
        </q-btn-group>
      </div>
    </q-menu>
  </q-btn>
</template>

<script>
import { defineComponent } from "@vue/composition-api"

export default defineComponent({
  name: "NotificationPopup",
  setup() {
    const items = [
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
        icon: "remove_red_eye",
      },
      {
        text: "has submitted",
        user: "sattler",
        object: "Quilting Our Philosophy",
        viewed: false,
        icon: "article",
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
        icon: "book",
      },
      {
        text: "has replied to your comment on",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
        icon: "reply",
      },
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
        icon: "remove_red_eye",
      },
      {
        text: "has submitted changes to",
        user: "sruthin",
        object: "A Whole New World",
        viewed: false,
        icon: "sync",
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
        icon: "book",
      },
      {
        text: "approved your submission,",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
        icon: "verified",
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
        icon: "book",
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: false,
        icon: "book",
      },
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
        icon: "book",
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: false,
        icon: "book",
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
        icon: "book",
      },
    ]

    return { items }
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
      margin-right: 10px
      width: 410px
      .q-badge
        position: absolute
        left: 0
        top: 50%
        background: $light-blue-5
        transform: translate(-50%, -50%)
        height: 100%
        padding:  4px
        border-radius: 3px
      span
        font-weight: bold
      &.unread
        background: $light-blue-1
</style>
