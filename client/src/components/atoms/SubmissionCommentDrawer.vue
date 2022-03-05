<template>
  <q-drawer
    :v-model="props.commentDrawerOpen"
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
        <submission-comment />
        <div class="q-ml-md q-mb-md">
          <submission-comment />
        </div>
        <div class="row justify-center q-pa-md">
          <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
        </div>
      </q-scroll-area>
    </div>
  </q-drawer>
</template>

<script setup>
import { ref } from "vue"
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
  modelValue: {
    type: Boolean,
    default: null,
  },
})
</script>
