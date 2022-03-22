<template>
  <div>
    <div class="row">
      <h2 class="q-pl-lg">My Notifications</h2>
    </div>
    <div class="row justify-center">
      <div class="col-md-8 q-pa-sm">
        <div class="row q-mb-md q-col-gutter-md justify-between items-center">
          <q-select
            v-model="filterMode"
            :options="filterModes"
            label="Filter"
            dense
            clearable
            outlined
            class="col-8 col-md-4"
          >
            <template #before>
              <q-icon name="filter_alt" />
            </template>
          </q-select>
          <div class="col-4 col-md-3">
            <q-btn dense class="full-width">Dismiss All</q-btn>
          </div>
        </div>
        <q-list class="notifications-list">
          <notification-list-item
            v-for="(item, index) in filteredItems"
            :key="index"
            :note="item"
            clickable
            class="q-pa-none q-pr-md"
            :class="{ unread: !item.read_at }"
            :icon-size="$q.screen.lt.md ? 'xs' : 'md'"
            show-time
            style="border-bottom: 1px solid #acd"
          />
        </q-list>
        <div class="row justify-center">
          <div class="q-pa-lg">
            <q-pagination v-model="currentPage" :max="5" class="col-12" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue"
import { useQuery, useResult } from "@vue/apollo-composable"
import { CURRENT_USER_NOTIFICATIONS } from "src/graphql/queries"
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"

const currentPage = ref(1)
const { result } = useQuery(CURRENT_USER_NOTIFICATIONS, {
  page: currentPage,
})
const notificationItems = useResult(
  result,
  [],
  (data) => data.currentUser.notifications.data
)

const filterMode = ref(null)
const filterModes = ["Unread", "Read"]
const filteredItems = computed(() => {
  if (!filterMode.value) {
    return notificationItems.value
  }

  const read = filterMode.value === "Read" ? true : false
  return notificationItems.value.filter((i) =>
    read ? i.read_at !== null : i.read_at === null
  )
})
</script>

<style lang="sass">
.notifications-list .q-item
    overflow: hidden

@media (max-width: $breakpoint-xs-max)
    .notifications-list .q-item
      min-height: 45px
      padding: 8px 10px 8px 0
</style>
