<template>
  <div>
    <div class="row">
      <h2 class="q-pl-lg">{{ $t("notifications.feed_page.heading") }}</h2>
    </div>
    <div class="row justify-center">
      <div class="col-md-8 q-pa-sm">
        <div class="row q-mb-md q-col-gutter-md justify-between items-center">
          <q-select
            v-model="filterMode"
            :options="filterModes"
            :label="$t(`notifications.feed_page.filter`)"
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
            <q-btn dense class="full-width">{{
              $t("notifications.dismiss_all")
            }}</q-btn>
          </div>
        </div>
        <q-list v-if="notificationItems.length" class="notifications-list">
          <notification-list-item
            v-for="(item, index) in notificationItems"
            :key="index"
            :note="item"
            clickable
            class="q-pa-none q-pr-md"
            :class="{ unread: !item.read_at }"
            :icon-size="screen.lt.md ? 'xs' : 'md'"
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
        <q-card v-else ref="default_message" class="q-py-xl text-center" flat>
          <p class="text-h3 text--grey">{{ $t("notifications.none") }}</p>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import NotificationListItem from "src/components/atoms/NotificationListItem.vue"
import {
  CurrentUserNotificationsDocument,
  CurrentUserNotificationsQueryVariables
} from "src/gql/graphql"

definePage({
  name: "feed"
})

const { screen } = useQuasar()

const filterMode = ref(null)
const filterModes = ["Unread", "Read"]

const currentPage = ref(1)

const variables = computed(() => {
  const vars: CurrentUserNotificationsQueryVariables = {
    page: currentPage.value
  }
  if (filterMode.value == "Read") {
    vars.read = true
  }
  if (filterMode.value == "Unread") {
    vars.unread = true
  }
  return vars
})
const { result } = useQuery(CurrentUserNotificationsDocument, variables)

const notificationItems = computed(() => {
  return result.value?.currentUser.notifications.data ?? []
})
const paginatorData = computed(() => {
  return (
    result.value?.currentUser.notifications.paginatorInfo ?? {
      count: 0,
      currentPage: 1,
      lastPage: 1,
      perPage: 10
    }
  )
})
const isPaginationVisible = computed(() => {
  return currentPage.value > 1 || notificationItems.value.length > 0
})
</script>

<script lang="ts">
graphql(`
  query CurrentUserNotifications($page: Int, $unread: Boolean, $read: Boolean) {
    currentUser {
      id
      notifications(first: 10, page: $page, unread: $unread, read: $read) {
        paginatorInfo {
          ...PaginationFields
        }
        data {
          id
          read_at
          created_at
          data {
            user {
              username
            }
            submission {
              title
            }
            invitee {
              display_label
            }
            inviter {
              display_label
            }
            publication {
              name
            }
            type
            body
          }
        }
      }
    }
  }
`)
</script>
<style lang="sass">
.notifications-list .q-item
    overflow: hidden

@media (max-width: $breakpoint-xs-max)
    .notifications-list .q-item
      min-height: 45px
      padding: 8px 10px 8px 0
</style>
