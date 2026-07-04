import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it, vi, beforeEach } from "vitest"
import { ref, type Ref } from "vue"

// Controllable stand-ins for the apollo composable so we can drive result,
// loading and fetchMore without a live client.
const state: {
  result: Ref<unknown>
  loading: Ref<boolean>
  fetchMore: ReturnType<typeof vi.fn>
} = {
  result: ref(undefined),
  loading: ref(false),
  fetchMore: vi.fn()
}

vi.mock("@vue/apollo-composable", () => ({
  useQuery: () => ({
    result: state.result,
    loading: state.loading,
    fetchMore: state.fetchMore
  })
}))

import SubmissionsFilterPanelPublication from "./SubmissionsFilterPanelPublication.vue"

installQuasarPlugin()

function resultWith(
  data: { id: string; name: string }[],
  hasMorePages = false,
  currentPage = 1
) {
  return {
    publications: {
      data,
      paginatorInfo: { count: data.length, currentPage, hasMorePages }
    }
  }
}

function factory() {
  return mount(SubmissionsFilterPanelPublication, {
    props: { modelValue: null, "onUpdate:modelValue": () => {} },
    global: {
      mocks: { $t: (token: string) => token }
    }
  })
}

beforeEach(() => {
  state.result.value = undefined
  state.loading.value = false
  state.fetchMore = vi.fn()
})

describe("SubmissionsFilterPanelPublication", () => {
  it("renders no options before any result arrives", () => {
    const wrapper = factory()
    expect(
      wrapper.findComponent({ name: "QSelect" }).props("options") as unknown[]
    ).toEqual([])
  })

  it("maps publication results to label/value options", () => {
    state.result.value = resultWith([
      { id: "1", name: "Journal A" },
      { id: "2", name: "Journal B" }
    ])
    const wrapper = factory()
    expect(wrapper.findComponent({ name: "QSelect" }).props("options")).toEqual(
      [
        { label: "Journal A", value: "1" },
        { label: "Journal B", value: "2" }
      ]
    )
  })

  it("fetches the next page when scrolled to the last option", () => {
    state.result.value = resultWith([{ id: "1", name: "A" }], true, 1)
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })
    expect(state.fetchMore).toHaveBeenCalledTimes(1)
    expect(state.fetchMore.mock.calls[0][0].variables).toEqual({
      page: 2,
      first: 15
    })
  })

  it("does not fetch more when not scrolled to the last option", () => {
    state.result.value = resultWith(
      [
        { id: "1", name: "A" },
        { id: "2", name: "B" }
      ],
      true,
      1
    )
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })
    expect(state.fetchMore).not.toHaveBeenCalled()
  })

  it("does not fetch more when there are no further pages", () => {
    state.result.value = resultWith([{ id: "1", name: "A" }], false, 1)
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })
    expect(state.fetchMore).not.toHaveBeenCalled()
  })

  it("does not fetch more while a request is already loading", () => {
    state.result.value = resultWith([{ id: "1", name: "A" }], true, 1)
    state.loading.value = true
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })
    expect(state.fetchMore).not.toHaveBeenCalled()
  })

  it("merges fetched pages via updateQuery", () => {
    state.result.value = resultWith([{ id: "1", name: "A" }], true, 1)
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })

    const updateQuery = state.fetchMore.mock.calls[0][0].updateQuery
    const prev = resultWith([{ id: "1", name: "A" }], true, 1)
    const next = resultWith([{ id: "2", name: "B" }], false, 2)

    const merged = updateQuery(prev, { fetchMoreResult: next })
    expect(merged.publications.data).toEqual([
      { id: "1", name: "A" },
      { id: "2", name: "B" }
    ])
    expect(merged.publications.paginatorInfo.currentPage).toBe(2)
  })

  it("updateQuery returns the previous result when there is nothing new", () => {
    state.result.value = resultWith([{ id: "1", name: "A" }], true, 1)
    const wrapper = factory()
    wrapper
      .findComponent({ name: "QSelect" })
      .vm.$emit("virtual-scroll", { to: 0 })

    const updateQuery = state.fetchMore.mock.calls[0][0].updateQuery
    const prev = resultWith([{ id: "1", name: "A" }], true, 1)
    expect(updateQuery(prev, { fetchMoreResult: null })).toBe(prev)
  })
})
