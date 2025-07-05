<template>
  <q-td :props="scope">
    <q-item class="q-pa-none">
      <q-item-section>
        <q-item-label>
          <slot name="value" v-bind="scope">
            {{ scope.value }}
          </slot>
        </q-item-label>
        <q-item-label caption>
          <slot name="aside" v-bind="scope">
            {{ asideLabel }}: {{ asideValue }}
          </slot>
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({
  scope: {
    type: Object,
    required: true
  }
})

const asideValue = computed(() => {
  if (typeof props.scope.col.aside === "function") {
    return props.scope.col.aside(props.scope.row)
  } else if (props.scope.col.aside) {
    return props.scope.col.aside
      .split(".")
      .reduce((o, i) => o[i], props.scope.row)
  } else {
    return ""
  }
})

const asideLabel = computed(() => {
  if (typeof props.scope.col.asideLabel === "function") {
    return props.scope.col.asideLabel(props.scope.row)
  } else if (props.scope.col.asideLabel) {
    return props.scope.col.asideLabel
  } else {
    return ""
  }
})
</script>
