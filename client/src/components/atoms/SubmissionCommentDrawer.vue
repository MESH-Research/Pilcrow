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
        style="width: 6px; cursor: col-resize"
        class="bg-primary"
      ></div>
      <q-scroll-area class="fit col bg-grey-4">
        <div class="q-px-md">
          <h3 id="inline_comments">Inline Comments</h3>
        </div>
        <submission-comment />
        <submission-comment />
        <div class="row justify-center q-pa-md">
          <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
        </div>
      </q-scroll-area>
    </div>
  </q-drawer>
</template>

<script setup>
import { ref, watch } from "vue"
import SubmissionComment from "src/components/atoms/SubmissionComment.vue"
const drawerWidth = ref(400)
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
