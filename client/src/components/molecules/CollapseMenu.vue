<template>
  <div>
    <q-list bordered class="gt-xs">
      <q-item v-for="(item, index) in items" :key="index" :to="item.url">
        <q-item-section v-if="item.icon" avatar
          ><q-icon :name="item.icon"
        /></q-item-section>
        <q-item-section>{{ item.label }}</q-item-section>
      </q-item>
    </q-list>
    <q-btn class="lt-sm full-width" :label="activeRoute.label">
      <q-menu>
        <q-list class="full-width">
          <q-item
            v-for="(item, index) in items"
            :key="index"
            clickable
            :to="item.url"
          >
            <q-item-section>{{ item.label }}</q-item-section>
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
      return props.items.find((e) => e.url === currentPath.value)
    })

    function isActive(url) {
      return url === currentPath.value
    }
    return { isActive, activeRoute }
  },
}
</script>
