<template>
  <div>
    <q-list bordered class="gt-xs">
      <q-item v-for="(item, index) in items" :key="index" :to="item.url">
        <q-item-section v-if="item.icon" avatar
          ><q-icon :name="item.icon"
        /></q-item-section>
        <q-item-section>{{ item.label }}</q-item-section>
        <q-item-section v-if="item.problem" side>
          <q-icon name="warning" color="accent" size="xs" />
          <q-tooltip v-if="item.problemTooltip">
            {{ item.problemTooltip }}
          </q-tooltip>
        </q-item-section>
      </q-item>
    </q-list>
    <q-btn
      v-if="activeRoute"
      class="lt-sm full-width"
      :label="activeRoute.label"
    >
      <q-menu>
        <q-list class="full-width">
          <q-item
            v-for="(item, index) in items"
            :key="index"
            clickable
            :to="item.url"
          >
            <q-item-section>{{ item.label }}</q-item-section>
            <q-item-section v-if="item.problem" side>
              <q-icon name="warning" color="orange" />
              <q-tooltip v-if="item.problemTooltip">
                {{ item.problemTooltip }}
              </q-tooltip>
            </q-item-section>
          </q-item>
        </q-list>
      </q-menu>
    </q-btn>
  </div>
</template>

<script>
import { computed } from "vue"
import { useRoute } from "vue-router"
export default {
  props: {
    items: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
    const route = useRoute()
    const currentPath = computed(() => route.path)
    const activeRoute = computed(() => {
      return props.items.find((e) => isActive(e.url))
    })

    function isActive(url) {
      if (typeof url === "string") return url === currentPath.value
      if (url.name) {
        return url.name === route.name
      }
    }
    return { isActive, activeRoute }
  },
}
</script>
