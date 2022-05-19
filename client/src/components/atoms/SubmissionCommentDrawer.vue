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
            v-if="false"
            class="q-ma-md q-pa-md bg-grey-1"
            bordered
            style="border-color: rgb(56, 118, 187)"
          >
            <comment-editor :is-inline-comment="true" />
          </q-card>
          <submission-comment
            v-for="comment in comments"
            ref="commentsRefs"
            :key="comment.id"
            :comment="comment"
            is-inline-comment
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
import { ref, watch, inject, nextTick } from "vue"
import SubmissionComment from "src/components/atoms/SubmissionComment.vue"
import CommentEditor from "src/components/forms/CommentEditor.vue"
import { scroll } from "quasar"

const { getScrollTarget, setVerticalScrollPosition } = scroll

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
const comments = inject("comments")
const commentsRefs = ref([])
const activeComment = inject("activeComment")

watch(activeComment, (newValue) => {
  if (!newValue) return
  nextTick(() => {
    //TODO: There's a potential problem here since per Vue's docs the order of refs in the array is not guarenteed to match the order of the v-fot array
    //TODO: Solution: Since the ref is a component ref, expose an id that can be used to find the correct component directly in the refs array
    const index = comments.value.findIndex((o) => o.id === newValue)
    const el = commentsRefs.value[index].scrollTarget
    const target = getScrollTarget(el)
    const offset = el.offsetTop
    setVerticalScrollPosition(target, offset, 250)
  })
})
</script>
