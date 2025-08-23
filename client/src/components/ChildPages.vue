<template>
  <q-list class="child-list">
    <slot name="prepend" />
    <slot :pages="mergedTabs">
      <template v-for="tab in mergedTabs" :key="tab.name">
        <slot name="item" :tab>
          <q-item :key="tab.name" class="child-item" :to="tab.to" exact>
            <q-item-section class="col-auto">
              <q-icon size="22px" :name="tab.icon" />
            </q-item-section>
            <q-item-section :class="labelClass" class="no-wrap">
              {{ tab.label }}
            </q-item-section>
          </q-item>
        </slot>
      </template>
    </slot>
    <slot name="append" />
  </q-list>
</template>

<script setup lang="ts">
import { type ChildRoute, useNavigation } from "src/use/navigation"
import type { RouteLocationRaw as TypedRouteLocationRaw } from "vue-router/auto"

interface Props {
  route: TypedRouteLocationRaw
  labelClass?: string | object
  append?: ChildRoute[]
}

const props = defineProps<Props>()

const { childrenOf } = useNavigation()

const tabs = childrenOf(props.route as TypedRouteLocationRaw)

const mergedTabs = computed((): ChildRoute[] =>
  tabs.value.concat(props.append ?? [])
)
</script>
