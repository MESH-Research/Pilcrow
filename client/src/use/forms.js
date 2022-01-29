import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import { computed, onMounted, onUnmounted } from "vue"
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
      component: DiscardChangesDialog,
    })
  }

  onBeforeRouteLeave(async (_, __, next) => {
    if (!dirtyRef.value) {
      return next()
    }
    return await dirtyDialog()
      .onOk(function () {
        next()
      })
      .onCancel(function () {
        next(false)
      })
  })

  onMounted(() => {
    window.addEventListener("beforeunload", beforeUnload)
  })
  onUnmounted(() => {
    window.removeEventListener("beforeunload", beforeUnload)
  })
}

export function useFormState(dirtyRef, savedRef, queries, mutations) {
  const queryLoading = computed(() => {
    if (Array.isArray(queries)) {
      if (queries.find((i) => i.loading.value)) {
        return true
      }
      return false
    }
    return queries.loading
  })

  const mutationLoading = computed(() => {
    if (Array.isArray(mutations)) {
      if (mutations.find((i) => i.loading.value)) {
        return true
      }
      return false
    }
    return mutations.loading
  })

  return computed(() => {
    if (mutationLoading.value) {
      return "saving"
    }
    if (queryLoading.value) {
      return "loading"
    }
    if (dirtyRef.value) {
      return "dirty"
    }
    if (savedRef.value) {
      return "saved"
    }
    return "idle"
  })
}
export default { useDirtyGuard, useFormState }
