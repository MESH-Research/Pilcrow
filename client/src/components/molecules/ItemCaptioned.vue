<template>
  <q-item class="q-pa-none">
    <q-item-section>
      <slot name="value" :pt="pt">
        <q-item-label>{{ value }}</q-item-label>
      </slot>
      <slot name="caption" :pt="pt">
        <q-item-label caption>
          <q-icon v-if="icon" :name="icon" />
          {{ pt(tCaption) }}
        </q-item-label>
      </slot>
    </q-item-section>
  </q-item>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useI18nPrefix } from "src/use/i18nPrefix"

interface Props {
  tPrefix: string
  tCaption: string
  value?: string
  icon?: string | null
}

const props = withDefaults(defineProps<Props>(), {
  value: "",
  icon: null
})

const { pt } = useI18nPrefix(computed(() => props.tPrefix))
</script>
