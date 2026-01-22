import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import { onMounted, onUnmounted } from "vue"
import { onBeforeRouteLeave } from "vue-router"
import { useQuasar } from "quasar"

export function useDirtyGuard(dirtyRef) {
  const { dialog } = useQuasar()
  function beforeUnload(e) {
    if (dirtyRef.value) {
      e.preventDefault()
      e.returnValue = ""
      return "Are you sure you want to leave this page? (Changes might be lost)"
    }
  }
  function dirtyDialog() {
    return dialog({
      component: DiscardChangesDialog
    })
  }

  onBeforeRouteLeave(() => {
    return new Promise((resolve) => {
      if (!dirtyRef.value) {
        resolve(true)

        return
      }
      dirtyDialog()
        .onOk(function () {
          resolve(true)
        })
        .onCancel(function () {
          resolve(false)
        })
    })
  })

  onMounted(() => {
    window.addEventListener("beforeunload", beforeUnload)
  })
  onUnmounted(() => {
    window.removeEventListener("beforeunload", beforeUnload)
  })
}
