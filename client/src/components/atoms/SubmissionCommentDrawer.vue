<template>
  <q-drawer
    v-model="DrawerOpen"
    show-if-above
    side="right"
    bordered
    :width="drawerWidth"
  >
    <div class="row fit">
      <div
        v-touch-pan.horizontal.prevent.mouse.preserveCursor="handlePan"
        style="width: 12px; cursor: col-resize"
        class="bg-primary column items-center justify-center"
      >
        <q-icon name="fas fa-grip-lines-vertical" color="white" size="12px" />
      </div>
      <q-scroll-area class="fit col bg-grey-4">
        <section>
          <div class="q-px-md">
            <h3 id="inline_comments" class="q-mb-sm">Commenters</h3>
          </div>
          <div class="bg-white q-pa-md">
            <user-list data-cy="list_commenters" :users="users" />
            <q-pagination
              v-model="currentPage"
              class="q-pa-md flex flex-center"
              :max="lastPage"
            />
          </div>
        </section>
        <div class="q-px-md">
          <h3 id="inline_comments" class="q-mb-sm">Inline Comments</h3>
        </div>
        <submission-comment />
        <submission-comment />
        <div class="row justify-center q-pa-md q-pb-xl">
          <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
        </div>
      </q-scroll-area>
    </div>
  </q-drawer>
</template>

<script setup>
import { ref, watch } from "vue"
import { GET_USERS } from "src/graphql/queries"
import { useQuery, useResult } from "@vue/apollo-composable"
import SubmissionComment from "src/components/atoms/SubmissionComment.vue"
import UserList from "src/components/molecules/UserList.vue"
const currentPage = ref(1)
const { result } = useQuery(GET_USERS, { page: currentPage })
const users = useResult(result, [], (data) => data.userSearch.data)
const lastPage = useResult(
  result,
  1,
  (data) => data.userSearch.paginatorInfo.lastPage
)

const drawerWidth = ref(440)
let originalWidth
let originalLeft
function handlePan({ ...newInfo }) {
  if (newInfo.isFirst) {
    originalWidth = drawerWidth.value
    originalLeft = newInfo.position.left
  } else {
    const newDelta = newInfo.position.left - originalLeft
    const newWidth = Math.max(200, Math.min(800, originalWidth - newDelta))
    drawerWidth.value = newWidth
  }
}
const props = defineProps({
  // Drawer status
  commentDrawerOpen: {
    type: Boolean,
    default: null,
  },
})
const DrawerOpen = ref(props.commentDrawerOpen)
watch(props, () => {
  DrawerOpen.value = props.commentDrawerOpen
})
</script>
