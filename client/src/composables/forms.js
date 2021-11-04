import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import { onMounted, onUnmounted } from "@vue/composition-api"
import { onBeforeRouteLeave } from "src/composables/router"

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

  onBeforeRouteLeave((_, __, next) => {
    if (!dirtyRef.value) {
      return next()
    }
    dirtyDialog()
      .onOk(function () {
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
