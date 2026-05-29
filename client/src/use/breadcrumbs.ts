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
import { useI18n } from "vue-i18n"

declare module "vue-router" {
  interface RouteMeta {
    crumb?: Crumb | Crumb[] | false
  }
}

export interface Crumb {
  // i18n key resolved via $t in useCrumbs. Override via setCrumbLabel
  // when the final label is dynamic (e.g. a fetched user name).
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
    const { t } = useI18n()
    started = true
    watchEffect(() => {
      const list: ResolvedCrumb[] = []
      for (const r of route.matched) {
        if (!r.meta.crumb) continue
        // meta.crumb can be a single crumb or an array — the array
        // form lets one matched route contribute multiple crumbs
        // (e.g. a detail page that stacks "Submitters → {user}"
        // without needing a parent layout file to back the first).
        const defs = Array.isArray(r.meta.crumb) ? r.meta.crumb : [r.meta.crumb]
        const ownerName = r.name as keyof RouteNamedMap | undefined
        for (let i = 0; i < defs.length; i++) {
          const def = defs[i]
          const isOwnCrumb = i === defs.length - 1
          const to: RouteLocationRaw =
            def.to ?? ({ name: r.name } as RouteLocationRaw)
          const resolved = router.resolve(to)
          const label = computed<string>(() => {
            // Only the last crumb a route contributes accepts a
            // dynamic label override via setCrumbLabel — the
            // preceding entries are static parent links.
            if (isOwnCrumb && ownerName) {
              const override = labelRegistry[ownerName]
              const unwrapped =
                override && typeof override === "object" && "value" in override
                  ? override.value
                  : (override as string | undefined)
              if (unwrapped !== undefined) return unwrapped
            }
            return t(def.label)
          })
          list.push({ label, to: resolved, icon: def.icon })
        }
      }
      crumbs.value = list
    })
  }
  const count = computed(() => crumbs.value.length)
  return { crumbs, count }
}
