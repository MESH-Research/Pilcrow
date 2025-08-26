import type { MaybeRef } from "vue"
import type { RouteLocationRaw, RouteLocationResolved } from "vue-router/auto"
import type { RouteNamedMap } from "vue-router/auto-routes"
import type { AppIconName } from "./appIcons"
import { getRouteIcon } from "./appIcons"
declare module "vue-router" {
  interface RouteMeta {
    crumb?: Crumb | false
  }
}

export type Crumb = {
  /**
   * Label for the crumb
   */
  label: string
  /**
   * Destination for the crumb
   */
  to?: RouteLocationRaw | (() => RouteLocationRaw)
  /**
   * Icon to add before brumbcrumb elements
   */
  icon?: string
  /**
   * The name of the mapped application icon to display.
   */
  appIcon?: AppIconName
}

export type BreadcrumbRuntime = Partial<
  Record<keyof RouteNamedMap, string | undefined>
>

export type ResolvedCrumb = {
  label: Ref<string>
  to: RouteLocationResolved
  icon?: string
}

const scope = shallowReactive<BreadcrumbRuntime>({})

const crumbs = shallowRef<ResolvedCrumb[]>([])
const count = computed(() => crumbs.value.length)

export function setCrumbLabel(
  name: keyof RouteNamedMap,
  value: MaybeRef<string | undefined>
) {
  // typing to string as reactive unreffing doens't appear in the typescript types
  // @see https://vuejs.org/guide/essentials/reactivity-fundamentals.html#ref-unwrapping-as-reactive-object-property
  scope[name] = toRef(value) as unknown as string
}

export function useCrumbs() {
  const route = useRoute()
  const router = useRouter()

  watchEffect(() => {
    const matched = route.matched

    crumbs.value = matched
      .filter((r) => r.meta.crumb)
      .map<ResolvedCrumb>((r) => {
        const definition = r.meta.crumb as Crumb
        const dest = toValue(definition.to) ?? { name: r.name }
        const to = router.resolve(dest as RouteLocationRaw)
        const label = computed(
          () => scope[r.name] ?? definition.label ?? to.name
        )
        const icon = definition.icon ?? getRouteIcon(to)
        return {
          ...definition,
          to,
          label,
          icon
        }
      })
  })

  return { crumbs, count }
}
