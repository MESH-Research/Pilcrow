<template>
  <div>
    <div class="row">
      <h2 class="q-pl-lg">My Notifications</h2>
    </div>
    <div class="row justify-center">
      <q-select
        v-model="filterMode"
        :options="filterModes"
        label="Filter"
        square
        filled
        clearable
        class="col-md-8 q-mb-md"
      >
        <template #before>
          <q-icon name="filter_alt" />
        </template>
      </q-select>
      <q-list class="col-md-8 notifications-list">
        <notification-list-item
          v-for="(item, index) in filteredItems"
          :key="index"
          class="q-pa-none q-pr-md"
          style="border-bottom: 1px solid #acd"
          :note="item"
          show-time
          icon-size="md"
        />
      </q-list>
    </div>
    <div class="q-pa-lg flex flex-center">
      <q-pagination v-model="currentPage" :max="5" />
    </div>
  </div>
</template>

<script>
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"
import { notificationItems } from "src/graphql/fillerData"
import { ref, computed } from "@vue/composition-api"

export default {
  components: { NotificationListItem },
  setup() {
    const filterMode = ref(null)
    const currentPage = ref(1)

    const filterModes = ["Unread", "Read"]

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
.q-item
    overflow: hidden
</style>
