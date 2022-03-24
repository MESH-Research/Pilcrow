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
          <div v-if="hasUnreadNotifications" class="col-4 col-md-3">
            <q-btn dense class="full-width">{{
              $t("notifications.dismiss_all")
            }}</q-btn>
          </div>
        </div>
        <q-list class="notifications-list">
          <notification-list-item
            v-for="(item, index) in notificationItems"
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
        <div v-if="isPaginationVisible" class="row justify-center">
          <div class="q-pa-lg">
            <q-pagination
              v-model="currentPage"
              :max="paginatorData.lastPage"
              class="col-12"
            />
          </div>
        </div>
        <div v-else class="q-pa-xl text-center">
          <p class="text-h3 text-grey">{{ $t("notifications.none") }}</p>
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

const filterMode = ref(null)
const filterModes = ["Unread", "Read"]

const currentPage = ref(1)

const variables = computed(() => {
  const vars = { currentPage: currentPage.value }
  if (filterMode.value == "Read") {
    vars.read = true
  }
  if (filterMode.value == "Unread") {
    vars.unread = true
  }
  return vars
})
const { result } = useQuery(CURRENT_USER_NOTIFICATIONS, variables)
const notificationItems = useResult(
  result,
  [],
  (data) => data.currentUser.notifications.data
)
const paginatorData = useResult(
  result,
  { count: 0, currentPage: 1, lastPage: 1, perPage: 10 },
  (data) => data.currentUser.notifications.paginatorInfo
)
const isPaginationVisible = computed(() => {
  if (currentPage.value > 1 || filteredItems.value.length > 0) {
    return true
  }
  return false
})
const hasUnreadNotifications = computed(() => {
  return notificationItems.value.length > 0 &&
    notificationItems.value.find((item) => item.read_at === null)
    ? true
    : false
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
