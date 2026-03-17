<template>
  <div class="field-display row items-start q-gutter-x-sm">
    <q-icon v-if="icon" :name="icon" size="xs" class="field-display__icon" />
    <div class="col">
      <div class="field-display__label text-caption text-weight-medium text-grey-7">
        {{ label }}
      </div>
      <div class="field-display__value" :class="valueClass">
        <slot>{{ value }}</slot>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string
  value?: string
  icon?: string
  valueClass?: string
}

withDefaults(defineProps<Props>(), {
  value: "",
  icon: undefined,
  valueClass: "text-body1"
})
</script>

<style scoped>
.field-display__icon {
  margin-top: 2px;
}

.field-display__label {
  line-height: 1.2;
  margin-bottom: 2px;
}
</style>
