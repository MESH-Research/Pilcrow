import DiscardChangesDialog from "src/components/dialogs/DiscardChangesDialog.vue"
import { computed, onMounted, onUnmounted, ref } from "vue"
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

export function useFormState(queryLoadingRef, mutationLoadingRef) {
  const dirty = ref(false)
  const saved = ref(false)
  const errorMessage = ref("")
  const state = computed(() => {
    if (mutationLoadingRef.value) {
      return "saving"
    }
    if (queryLoadingRef.value) {
      return "loading"
    }
    if (errorMessage.value) {
      return "error"
    }
    if (dirty.value) {
      return "dirty"
    }
    if (saved.value) {
      return "saved"
    }
    return "idle"
  })
  return {
    state,
    saved,
    dirty,
    queryLoading: queryLoadingRef,
    mutationLoading: mutationLoadingRef,
    errorMessage,
  }
}
