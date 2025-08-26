import type { RouteLocationRaw, RouteLocationResolved } from "vue-router/auto"
import { getRouteIcon } from "./appIcons"
declare module "vue-router" {
  interface RouteMeta {
    navigation?: {
      label: string | false
      icon?: string
    }
  }
}

export function useNavigation() {
  const router = useRouter()

  return {
    childrenOf: function (
      route: RouteLocationRaw,
      slice: number = -1
    ): ComputedRef<ChildRoute[]> {
      const resolved = router.resolve(route)
      console.log(resolved)
      const children = computed(() => resolved.matched.slice(slice)[0].children)
      return computed(() => {
        return children.value
          .map((route) => router.resolve(route))
          .filter((route) => route.meta.navigation?.label !== false)
          .map((route) => {
            const { meta, name } = route
            return {
              name,
              label: meta.navigation?.label ?? (name as string),
              icon: meta.navigation?.icon ?? getRouteIcon(route),
              to: route
            }
          })
      })
    }
  }
}

export type ChildRoute = {
  name: RouteLocationResolved["name"]
  label: string | false
  icon: string | undefined
  to: RouteLocationRaw
}
