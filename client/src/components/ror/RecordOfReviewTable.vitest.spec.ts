import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { defineComponent } from "vue"
import { beforeEach, describe, expect, it, vi } from "vitest"
import gql from "graphql-tag"
import { post_review_states } from "src/utils/postReviewStates"
import { defaultOptions as defaultRoleOptions } from "src/pages/Admin/components/SubmissionsFilterPanelRoles.vue"
import RecordOfReviewTable from "./RecordOfReviewTable.vue"

installQuasarPlugin()

// Mutable route query the component reads on setup; reset before each test.
let routeQuery: Record<string, string> = {}
const replace = vi.fn()

vi.mock("vue-router", () => ({
  useRoute: () => ({
    get query() {
      return routeQuery
    }
  }),
  useRouter: () => ({ replace })
}))

const QueryTableStub = defineComponent({
  name: "QueryTable",
  props: {
    variables: { type: Object, default: () => ({}) },
    enabled: { type: Boolean, default: false }
  },
  // Render the top-after slot so the filter panel mounts and can emit.
  data: () => ({ page: 1 }),
  template: `<div class="qt-stub"><slot name="top-after" /></div>`
})

const FilterPanelStub = defineComponent({
  name: "SubmissionsFilterPanel",
  props: ["statusFilter", "roleFilter", "publicationFilter", "allowedStatuses"],
  emits: [
    "update:statusFilter",
    "update:roleFilter",
    "update:publicationFilter"
  ],
  template: `<div class="panel-stub" />`
})

const query = gql`
  query {
    __typename
  }
`

async function mountTable() {
  const wrapper = mount(RecordOfReviewTable, {
    props: { query },
    global: {
      stubs: {
        QueryTable: QueryTableStub,
        SubmissionsFilterPanel: FilterPanelStub,
        "router-link": true
      }
    }
  })
  await flushPromises()
  return wrapper
}

const queryTable = (wrapper: Awaited<ReturnType<typeof mountTable>>) =>
  wrapper.findComponent(QueryTableStub)
const panel = (wrapper: Awaited<ReturnType<typeof mountTable>>) =>
  wrapper.findComponent(FilterPanelStub)

beforeEach(() => {
  routeQuery = {}
  replace.mockClear()
})

describe("RecordOfReviewTable initial state", () => {
  it("defaults to all post-review statuses and roles when the URL is empty", async () => {
    const wrapper = await mountTable()
    const vars = queryTable(wrapper).props("variables")
    expect(vars.status).toEqual(post_review_states)
    expect(vars.roles).toEqual(defaultRoleOptions)
    expect(vars.publication).toBeUndefined()
    expect(queryTable(wrapper).props("enabled")).toBe(true)
  })

  it("seeds the filters from the route query", async () => {
    routeQuery = {
      status: "[ACCEPTED_AS_FINAL,REJECTED]",
      roles: "[reviewer]",
      publication: "7"
    }
    const wrapper = await mountTable()
    const vars = queryTable(wrapper).props("variables")
    expect(vars.status).toEqual(["ACCEPTED_AS_FINAL", "REJECTED"])
    expect(vars.roles).toEqual(["reviewer"])
    expect(vars.publication).toEqual(["7"])
  })

  it("drops query values that are not valid statuses or roles", async () => {
    routeQuery = {
      status: "[ACCEPTED_AS_FINAL,BOGUS]",
      roles: "[reviewer,nope]"
    }
    const wrapper = await mountTable()
    const vars = queryTable(wrapper).props("variables")
    expect(vars.status).toEqual(["ACCEPTED_AS_FINAL"])
    expect(vars.roles).toEqual(["reviewer"])
  })

  it("disables the query when either filter group is empty", async () => {
    routeQuery = { status: "[]" }
    const wrapper = await mountTable()
    expect(queryTable(wrapper).props("enabled")).toBe(false)
  })
})

describe("RecordOfReviewTable URL sync", () => {
  it("writes a non-default status filter to the query and omits defaults", async () => {
    const wrapper = await mountTable()
    panel(wrapper).vm.$emit("update:statusFilter", ["ACCEPTED_AS_FINAL"])
    await flushPromises()
    expect(replace).toHaveBeenCalledTimes(1)
    expect(replace.mock.calls[0][0]).toEqual({
      query: { status: "[ACCEPTED_AS_FINAL]" }
    })
  })

  it("removes the status param when filters return to the default set", async () => {
    routeQuery = { status: "[ACCEPTED_AS_FINAL]" }
    const wrapper = await mountTable()
    panel(wrapper).vm.$emit("update:statusFilter", [...post_review_states])
    await flushPromises()
    expect(replace.mock.calls.at(-1)?.[0].query).not.toHaveProperty("status")
  })

  it("writes and clears the publication param", async () => {
    const wrapper = await mountTable()
    panel(wrapper).vm.$emit("update:publicationFilter", "12")
    await flushPromises()
    expect(replace.mock.calls.at(-1)?.[0].query.publication).toBe("12")

    panel(wrapper).vm.$emit("update:publicationFilter", null)
    await flushPromises()
    expect(replace.mock.calls.at(-1)?.[0].query).not.toHaveProperty(
      "publication"
    )
  })

  it("writes a non-default role filter to the query", async () => {
    const wrapper = await mountTable()
    panel(wrapper).vm.$emit("update:roleFilter", ["reviewer"])
    await flushPromises()
    expect(replace.mock.calls.at(-1)?.[0].query.roles).toBe("[reviewer]")
  })

  it("resets the QueryTable to page 1 when a filter changes", async () => {
    const wrapper = await mountTable()
    queryTable(wrapper).vm.page = 5
    panel(wrapper).vm.$emit("update:roleFilter", ["reviewer"])
    await flushPromises()
    expect(queryTable(wrapper).vm.page).toBe(1)
  })
})

describe("RecordOfReviewTable selection reset", () => {
  it("clears the selection when a filter changes so hidden rows can't strand", async () => {
    const wrapper = mount(RecordOfReviewTable, {
      props: {
        query,
        selected: [{ id: "1" }, { id: "2" }],
        "onUpdate:selected": (value: unknown[]) =>
          wrapper.setProps({ selected: value })
      },
      global: {
        stubs: {
          QueryTable: QueryTableStub,
          SubmissionsFilterPanel: FilterPanelStub,
          "router-link": true
        }
      }
    })
    await flushPromises()

    panel(wrapper).vm.$emit("update:statusFilter", ["ACCEPTED_AS_FINAL"])
    await flushPromises()
    expect(wrapper.props("selected")).toEqual([])
  })

  it("does not emit a redundant reset when nothing is selected", async () => {
    const wrapper = await mountTable()
    const updates = () =>
      wrapper.emitted("update:selected")?.length ?? 0
    const before = updates()
    panel(wrapper).vm.$emit("update:publicationFilter", "12")
    await flushPromises()
    expect(updates()).toBe(before)
  })
})
