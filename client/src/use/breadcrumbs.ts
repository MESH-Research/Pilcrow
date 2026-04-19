import {
  computed,
  shallowReactive,
  shallowRef,
  toRef,
  watchEffect,
  type MaybeRef,
  type Ref
} from "vue"
import type { RouteLocationRaw } from "vue-router"
import { useRoute, useRouter } from "vue-router"
import type { RouteNamedMap } from "vue-router/auto-routes"

declare module "vue-router" {
  interface RouteMeta {
    crumb?: Crumb | false
  }
}

export interface Crumb {
  label: string
  to?: RouteLocationRaw
  icon?: string
}

export interface ResolvedCrumb {
  label: Ref<string>
  to: RouteLocationRaw
  icon?: string
}

type CrumbLabelRegistry = Partial<
  Record<keyof RouteNamedMap, Ref<string | undefined> | string | undefined>
>

const labelRegistry = shallowReactive<CrumbLabelRegistry>({})
const crumbs = shallowRef<ResolvedCrumb[]>([])

let started = false

export function setCrumbLabel(
  name: keyof RouteNamedMap,
  value: MaybeRef<string | undefined>
) {
  labelRegistry[name] = toRef(value) as Ref<string | undefined>
}

export function useCrumbs() {
  if (!started) {
    const route = useRoute()
    const router = useRouter()
    started = true
    watchEffect(() => {
      crumbs.value = route.matched
        .filter((r) => r.meta.crumb)
        .map<ResolvedCrumb>((r) => {
          const def = r.meta.crumb as Crumb
          const to: RouteLocationRaw =
            def.to ?? ({ name: r.name } as RouteLocationRaw)
          const resolved = router.resolve(to)
          // Label overrides are keyed by the *owning* route's name
          // (the one whose meta.crumb produced this entry), not the
          // link target — pages declare labels for themselves.
          const ownerName = r.name as keyof RouteNamedMap | undefined
          const label = computed<string>(() => {
            const override = ownerName ? labelRegistry[ownerName] : undefined
            const unwrapped =
              override && typeof override === "object" && "value" in override
                ? override.value
                : (override as string | undefined)
            return unwrapped ?? def.label
          })
          return { label, to: resolved, icon: def.icon }
        })
    })
  }
  const count = computed(() => crumbs.value.length)
  return { crumbs, count }
}
