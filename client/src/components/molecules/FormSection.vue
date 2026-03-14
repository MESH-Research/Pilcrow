<template>
  <div class="q-mb-lg">
    <form-section-header v-if="$slots.header" :first-section="firstSection"
      ><slot name="header" />
    </form-section-header>
    <q-card-section class="q-col-gutter-y-lg">
      <slot></slot>
    </q-card-section>
  </div>
</template>

<script setup lang="ts">
import FormSectionHeader from "../atoms/FormSectionHeader.vue"

interface Props {
  firstSection?: boolean
}
withDefaults(defineProps<Props>(), {
  firstSection: false
})
</script>
