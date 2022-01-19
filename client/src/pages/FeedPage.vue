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
            class="q-pa-none q-pr-md"
            style="border-bottom: 1px solid #acd"
            :note="item"
            show-time
            :icon-size="$q.screen.lt.md ? 'xs' : 'md'"
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
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"
import { ref, computed } from "vue"
import { notificationItems } from "src/graphql/fillerData"
/**
 * Notification feed page
 */
/**
 * Current filter mode
 * @values null, 'Read', 'Unread'
 *  */
const filterMode = ref(null)
/**
 * Current page displayed
 * @TODO Implement pagination with graphql
 */
const currentPage = ref(1)

const filterModes = ["Unread", "Read"]

/**
 * Filtered items based on filterMode
 */
const filteredItems = computed(() => {
  if (!filterMode.value) {
    return notificationItems
  }

  const read = filterMode.value === "Read" ? true : false
  return notificationItems.filter((i) =>
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
