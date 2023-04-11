import {
  useFormState,
} from "./formState"
import { ref } from "vue"
import { describe, test, expect } from 'vitest'

describe("useFormState composable", () => {
  test("form states", () => {
    const queryLoading = ref(false),
      mutationLoading = ref(false)

    const result = useFormState({ loading: queryLoading }, { loading: mutationLoading })

    const { state, saved, dirty, errorMessage } = result

    expect(state.value).toBe("idle")
    queryLoading.value = true

    expect(state.value).toBe("loading")

    mutationLoading.value = true
    expect(state.value).toBe("saving")

    saved.value = true
    expect(state.value).toBe("saving")

    mutationLoading.value = false
    queryLoading.value = false
    expect(state.value).toBe("saved")

    saved.value = false
    dirty.value = true
    expect(state.value).toBe("dirty")

    dirty.value = false
    errorMessage.value = "error message"
    expect(state.value).toBe("error")
  })
})