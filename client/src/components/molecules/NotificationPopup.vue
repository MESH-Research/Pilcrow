<template>
  <q-btn flat padding="none" data-cy="dropdown_username" icon="notifications">
    <q-badge floating color="light-blue-3" rounded />
    <q-menu>
      <q-list
        role="navigation"
        aria-label="Dropdown Navigation"
        data-cy="headerUserMenu"
        style="width: 400px"
        bordered
        separator
      >
        <q-item
          v-for="(item, index) in items"
          :key="index"
          clickable
          to="/dashboard"
          :class="item.viewed ? '' : 'bg-green-1'"
          class="notification_item"
        >
          <q-item-section side="left" class="">
            <q-badge v-if="!item.viewed" color="green-10" rounded />
            &nbsp;
          </q-item-section>
          <q-item-section>
            <p class="q-pa-none q-ma-none">
              <span href="/">{{ item.user }}</span> {{ item.text }}
              <span href="#">{{ item.object }}</span>
            </p>
          </q-item-section>
        </q-item>
      </q-list>
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
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: false,
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
      {
        text: "has requested your review on",
        user: "adamsb",
        object: "A new submission",
        viewed: false,
      },
      {
        text: "has invited you to review",
        user: "sruthin",
        object: "A Whole New World",
        viewed: false,
      },
      {
        text: "has invited you to review this much longer thing that may not fit",
        user: "sruthin",
        object: "A Whole New World",
        viewed: true,
      },
    ]

    return { items }
  },
})
</script>

<style scoped>
span {
  text-dectoration: underline;
  color: #18453b;
  font-weight: bold;
}
</style>
