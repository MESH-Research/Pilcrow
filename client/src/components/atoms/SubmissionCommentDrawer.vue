<template>
  <q-drawer
    v-model="drawerOpen"
    show-if-above
    side="right"
    bordered
    :width="drawerWidth"
  >
    <div class="row fit">
      <div
        v-touch-pan.horizontal.prevent.mouse.preserveCursor="handlePan"
        style="width: 12px; cursor: col-resize"
        class="bg-secondary column items-center justify-center aside-bar"
      >
        <q-icon name="fas fa-grip-lines-vertical" color="white" size="12px" />
      </div>
      <inline-comments />
    </div>
  </q-drawer>
</template>

<script setup lang="ts">
import { ref, computed } from "vue"
import InlineComments from "../molecules/InlineComments.vue"

const props = defineProps({
  drawerOpen: {
    type: Boolean,
    required: false,
    default: false
  }
})

const emit = defineEmits(["update:drawerOpen"])

const drawerOpen = computed({
  get: () => props.drawerOpen,
  set: (value) => emit("update:drawerOpen", value)
})

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
</script>
