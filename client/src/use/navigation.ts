import {
  computed,
  defineAsyncComponent,
  type AsyncComponentLoader,
  type Component,
  type ComputedRef
} from "vue"
import {
  useRouter,
  type RouteLocationRaw,
  type RouteLocationResolved,
  type RouteMeta
} from "vue-router"

declare module "vue-router" {
  interface RouteMeta {
    /**
     * Opt-in metadata that exposes a route to {@link useNavigation}.
     * Pages without `navigation` are ignored by `childrenOf` and will
     * not appear in auto-generated submenus.
     */
    navigation?: {
      /**
       * Display label for the menu item. Pass an i18n key — callers
       * are expected to run it through `t()`. Set to `false` to keep
       * the route resolvable but hide it from menus (e.g. an index
       * route that should not show alongside its siblings).
       */
      label: string | false
      /** Quasar/Material icon name shown next to the label. */
      icon?: string
      /**
       * Sort key. Lower values render first. Routes without `order`
       * are pushed to the end. Ties preserve source order. Leave gaps
       * (10, 20, 30) so new entries can slot in without renumbering.
       */
      order?: number
    }
  }
}

export type ChildRoute = {
  name: RouteLocationResolved["name"]
  label: string
  icon: string | undefined
  url: RouteLocationRaw
  // Full resolved meta, so callers can filter on their own meta keys
  // (e.g. a beta sub-layout hiding `meta.feature.private` entries).
  meta: RouteMeta
  // The child's page component, ready to render inline via
  // `<component :is>`. Lets a parent build a list from file-based routes
  // and render the children itself instead of routing to them.
  component: Component | undefined
}

export function useNavigation() {
  const router = useRouter()

  function childrenOf(
    route: RouteLocationRaw,
    slice: number = -1
  ): ComputedRef<ChildRoute[]> {
    const resolved = router.resolve(route)
    const children = computed(
      () => resolved.matched.slice(slice)[0]?.children ?? []
    )
    return computed(() => {
      const resolvedChildren = children.value
        .map((r) => router.resolve(r as RouteLocationRaw))
        .filter((r) => r.meta.navigation?.label !== false)
      const indexed = resolvedChildren.map((r, i) => ({ r, i }))
      indexed.sort((a, b) => {
        const ao = a.r.meta.navigation?.order ?? Number.POSITIVE_INFINITY
        const bo = b.r.meta.navigation?.order ?? Number.POSITIVE_INFINITY
        return ao - bo || a.i - b.i
      })
      return indexed.map(({ r }) => {
        const loader = r.matched[r.matched.length - 1]?.components?.default
        return {
          name: r.name,
          label:
            (r.meta.navigation?.label as string | undefined) ??
            (r.name as string),
          icon: r.meta.navigation?.icon,
          url: { name: r.name } as RouteLocationRaw,
          meta: r.meta,
          // unplugin-vue-router records use lazy `() => import()` loaders,
          // so wrap in defineAsyncComponent for inline rendering.
          component: loader
            ? defineAsyncComponent(loader as AsyncComponentLoader)
            : undefined
        }
      })
    })
  }

  return { childrenOf }
}
