<template>
  <div
    class="row password-meter"
    :class="valid ? 'password-success' : 'password-error'"
  >
    <div
      v-for="(_, index) in new Array(max)"
      :key="index"
      :class="score > index ? 'active' : ''"
      class="col"
    />
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    score?: number
    max?: number
    valid?: boolean
  }>(),
  {
    score: 0,
    max: 4,
    valid: false
  }
)
</script>

<style lang="sass" scoped>
.password-meter
  .col
    height: 3px
    background: #8f8f8f
    margin: 3px
  &.password-success .active
    background: $positive
  &.password-error .active
    background: $negative
</style>
