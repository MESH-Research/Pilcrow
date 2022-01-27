import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import { onMounted, onUnmounted } from "vue"
import { onBeforeRouteLeave } from "vue-router"

export function useDirtyGuard(dirtyRef, { root }) {
  function beforeUnload(e) {
    if (dirtyRef.value) {
      e.preventDefault()
      e.returnValue = ""
    }
  }
  function dirtyDialog() {
    return root.$q.dialog({
      component: DiscardChangesDialog,
    })
  }

  const unregisterLeave = onBeforeRouteLeave(async (_, __, next) => {
    if (!dirtyRef.value) {
      unregisterLeave()
      return next()
    }
    dirtyDialog()
      .onOk(function () {
        unregisterLeave()
        next()
      })
      .onCancel(function () {
        next(false)
      })
  })

  onMounted(() => {
    window.addEventListener("beforeUnload", beforeUnload)
  })
  onUnmounted(() => {
    window.removeEventListener("beforeUnload", beforeUnload)
  })
}

export default { useDirtyGuard }
