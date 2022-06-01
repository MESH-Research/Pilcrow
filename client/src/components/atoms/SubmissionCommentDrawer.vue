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
          <q-card
            class="q-ma-md q-pa-md bg-grey-1"
            bordered
            style="border-color: rgb(56, 118, 187)"
          >
          </q-card>
          <inline-comment
            v-for="comment in inline_comments"
            :key="comment.id"
            :comment="comment"
          />
          <div class="row justify-center q-pa-md q-pb-xl">
            <q-btn color="dark" icon="arrow_upward">Scroll to Top</q-btn>
          </div>
        </section>
      </q-scroll-area>
    </div>
  </q-drawer>
</template>

<script setup>
import { ref, watch, inject, computed } from "vue"
import InlineComment from "src/components/atoms/InlineComment.vue"

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
const submission = inject("submission")

const inline_comments = computed(() => {
  return submission.value?.inline_comments ?? []
})
const DrawerOpen = ref(props.commentDrawerOpen)
watch(props, () => {
  DrawerOpen.value = props.commentDrawerOpen
})
</script>
