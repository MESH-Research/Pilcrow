<template>
  <q-item class="q-pa-none">
    <q-item-section>
      <slot name="value" :t="t">
        <q-item-label>{{ value }}</q-item-label>
      </slot>
      <slot name="caption" :t="t">
        <q-item-label caption>
          <q-icon v-if="false" :name="icon" />
          {{ t(tCaption) }}
        </q-item-label>
      </slot>
    </q-item-section>
  </q-item>
</template>

<script setup>
import { useI18nPrefix } from "src/use/useI18nPrefix"

const { t } = useI18nPrefix()
defineProps({
  tCaption: {
    required: true,
    type: String
  },
  value: {
    required: false,
    type: String,
    default: ""
  },
  icon: {
    required: false,
    default: null,
    type: String
  }
})
</script>
