<template>
  <div>
    <div class="row">
      <h2 class="q-pl-lg">My Notifications</h2>
    </div>
    <div class="row justify-center">
      <div class="col-md-8 q-pa-sm">
        <div class="row">
          <q-select
            v-model="filterMode"
            :options="filterModes"
            label="Filter"
            filled
            dense
            clearable
            class="q-mb-md col-md-3 col-xs-12"
          >
            <template #before>
              <q-icon name="filter_alt" />
            </template>
          </q-select>
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

<script>
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"
import { ref, computed } from "@vue/composition-api"
import { notificationItems } from "src/graphql/fillerData"
/**
 * Notification feed page
 */
export default {
  components: { NotificationListItem },
  setup() {
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
      return notificationItems.filter((i) => i.viewed === read)
    })
    return { filterMode, filterModes, filteredItems, currentPage }
  },
}
</script>

<style lang="sass">
.notifications-list .q-item
    overflow: hidden
</style>
