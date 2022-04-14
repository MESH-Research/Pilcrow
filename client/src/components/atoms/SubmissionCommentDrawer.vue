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
          <div id="inline_comments_section" class="q-pa-md">
            <span class="text-h3"> Inline Comments </span>
          </div>
          <comment-editor :submission="submission" />
          <submission-comment is-inline-comment />
          <submission-comment is-inline-comment />
          <div class="row justify-center q-pa-md q-pb-xl">
            <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
          </div>
        </section>
      </q-scroll-area>
    </div>
  </q-drawer>
</template>

<script setup>
import { ref, watch } from "vue"
import SubmissionComment from "src/components/atoms/SubmissionComment.vue"
import CommentEditor from "src/components/forms/CommentEditor.vue"

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
  submission: {
    type: Object,
    default: null,
  },
})
const DrawerOpen = ref(props.commentDrawerOpen)
watch(props, () => {
  DrawerOpen.value = props.commentDrawerOpen
})
</script>
