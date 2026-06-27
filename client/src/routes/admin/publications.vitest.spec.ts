import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { defineComponent, h, ref } from "vue"
import PublicationIndexPage from "./publications.vue"
import { Notify } from "quasar"
import {
  GetAdminPublicationsDocument,
  AdminPublicationsViewerAbilitiesDocument,
  type GetAdminPublicationsQuery,
  type AdminPublicationsViewerAbilitiesQuery
} from "src/graphql/generated/graphql"
import { beforeEach, describe, expect, it, test, vi } from "vitest"

let routeQuery: Record<string, unknown> = {}
const routerMock = { push: vi.fn(), replace: vi.fn() }

vi.mock("vue-router", () => ({
  useRouter: () => routerMock,
  useRoute: () => ({ query: routeQuery })
}))

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

function mockViewerCanCreatePublication(canCreate: boolean) {
  const response: { data: AdminPublicationsViewerAbilitiesQuery } = {
    data: {
      currentUser: {
        __typename: "User",
        id: "1",
        abilities: {
          __typename: "UserAbilities",
          publication_create: canCreate
        }
      }
    }
  }
  mockClient
    .getRequestHandler(AdminPublicationsViewerAbilitiesDocument)
    .mockResolvedValue(response)
}

beforeEach(() => {
  routeQuery = {}
  routerMock.push.mockReset()
  routerMock.replace.mockReset()
  // Default: viewer may create; the gating tests override this.
  mockViewerCanCreatePublication(true)
})

describe("publications page mount", () => {
  const makeWrapper = () =>
    mount(PublicationIndexPage, {
      global: { stubs: ["router-link"] }
    })

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("all existing publications appear within the table", async () => {
    const mockPublicationsResponse: { data: GetAdminPublicationsQuery } = {
      data: {
        publications: {
          data: [
            {
              id: "1",
              name: "Sample Publication 1",
              created_at: "2026-01-01T00:00:00.000000Z"
            },
            {
              id: "2",
              name: "Sample Publication 2",
              created_at: "2026-01-02T00:00:00.000000Z"
            },
            {
              id: "3",
              name: "Sample Publication 3",
              created_at: "2026-01-03T00:00:00.000000Z"
            },
            {
              id: "4",
              name: "Sample Publication 4",
              created_at: "2026-01-04T00:00:00.000000Z"
            }
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 4,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
            total: 4
          }
        }
      }
    }
    mockClient
      .getRequestHandler(GetAdminPublicationsDocument)
      .mockResolvedValue(mockPublicationsResponse)
    const wrapper = makeWrapper()
    await flushPromises()

    const rows = wrapper.findAll("tbody tr")
    expect(rows).toHaveLength(4)
  })
})

// Slot-rendering QueryTable stand-in so the page's filter/sort logic can be
// exercised without a live table or apollo round-trip.
const QueryTableStub = defineComponent({
  name: "QueryTable",
  props: {
    query: { type: Object, default: undefined },
    variables: { type: Object, default: () => ({}) },
    columns: { type: Array, default: () => [] },
    tPrefix: { type: String, default: "" },
    syncUrl: { type: Boolean, default: false },
    defaultSort: { type: Object, default: undefined }
  },
  emits: ["row-click"],
  setup(_props, { slots, expose }) {
    const page = ref(99)
    expose({ page })
    return () => h("div", { class: "qt-stub" }, [slots["top-after"]?.()])
  }
})

describe("publications page filter logic", () => {
  function factory() {
    return mount(PublicationIndexPage, {
      global: {
        stubs: {
          QueryTable: QueryTableStub,
          PublicationsFilterPanel: true,
          CreateForm: true,
          "router-link": true
        },
        mocks: { $t: (token: string) => token }
      }
    })
  }

  const stub = (wrapper: ReturnType<typeof factory>) =>
    wrapper.findComponent(QueryTableStub)

  it("sends no filter variables at defaults", () => {
    expect(stub(factory()).props("variables")).toEqual({})
  })

  it("maps visibility and accepting filters from the route query", () => {
    routeQuery = { visibility: "public", accepting: "no" }
    expect(stub(factory()).props("variables")).toEqual({
      public: true,
      accepting_submissions: false
    })
  })

  it("ignores invalid filter values in the route query", () => {
    routeQuery = { visibility: "bogus", accepting: "maybe" }
    expect(stub(factory()).props("variables")).toEqual({})
  })

  it("encodes hidden / yes filter combinations", () => {
    routeQuery = { visibility: "hidden", accepting: "yes" }
    expect(stub(factory()).props("variables")).toEqual({
      public: false,
      accepting_submissions: true
    })
  })

  it("derives column display values", () => {
    const columns = stub(factory()).props("columns") as {
      name: string
      field: ((row: Record<string, unknown>) => unknown) | string
    }[]
    const fieldFn = (n: string) =>
      columns.find((c) => c.name === n)!.field as (
        row: Record<string, unknown>
      ) => unknown

    expect(fieldFn("is_publicly_visible")({ is_publicly_visible: true })).toBe(
      "Public"
    )
    expect(fieldFn("is_publicly_visible")({ is_publicly_visible: false })).toBe(
      "Hidden"
    )
    expect(
      fieldFn("is_accepting_submissions")({ is_accepting_submissions: true })
    ).toBe("Yes")
    expect(
      fieldFn("is_accepting_submissions")({ is_accepting_submissions: false })
    ).toBe("No")
  })

  it("navigates to the publication home on row click", () => {
    stub(factory()).vm.$emit("row-click", new Event("click"), { id: "12" })
    expect(routerMock.push).toHaveBeenCalledWith({
      name: "publication:home",
      params: { id: "12" }
    })
  })

  it("syncs a non-default filter to the URL query", async () => {
    const wrapper = factory()
    const vm = wrapper.vm as unknown as { visibilityFilter: string }
    vm.visibilityFilter = "public"
    await flushPromises()
    const lastArg = routerMock.replace.mock.calls.at(-1)![0] as {
      query: Record<string, string>
    }
    expect(lastArg.query.visibility).toBe("public")
  })

  it("removes a filter from the URL query when reset to default", async () => {
    routeQuery = { visibility: "public" }
    const wrapper = factory()
    const vm = wrapper.vm as unknown as { visibilityFilter: string }
    vm.visibilityFilter = "all"
    await flushPromises()
    const lastArg = routerMock.replace.mock.calls.at(-1)![0] as {
      query: Record<string, string>
    }
    expect(lastArg.query.visibility).toBeUndefined()
  })

  it("closes the dialog and routes to setup when a publication is created", async () => {
    const wrapper = factory()
    const vm = wrapper.vm as unknown as { showCreateDialog: boolean }
    vm.showCreateDialog = true
    await flushPromises()
    wrapper
      .findComponent({ name: "CreateForm" })
      .vm.$emit("created", { id: "55" })
    expect(vm.showCreateDialog).toBe(false)
    expect(routerMock.push).toHaveBeenCalledWith({
      name: "publication:setup:basic",
      params: { id: "55" }
    })
  })

  it("shows the create button when the viewer holds publication_create", async () => {
    mockViewerCanCreatePublication(true)
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find('[data-cy="create_pub_button"]').exists()).toBe(true)
  })

  it("hides the create button when the viewer lacks publication_create", async () => {
    mockViewerCanCreatePublication(false)
    const wrapper = factory()
    await flushPromises()
    expect(wrapper.find('[data-cy="create_pub_button"]').exists()).toBe(false)
  })
})
