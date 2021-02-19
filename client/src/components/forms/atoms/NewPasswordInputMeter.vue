<template>
  <div
    class="row password-meter"
    :class="valid ? 'password-success' : 'password-error'"
  >
    <div
      v-for="(item, index) in new Array(max)"
      :key="index"
      :class="score > index ? 'active' : ''"
      class="col"
    />
  </div>
</template>

<script>
export default {
  name: "NewPasswordInputMeter",
  props: {
    score: {
      type: Number,
      default: 0
    },
    max: {
      type: Number,
      default: 4
    },
    valid: {
      type: Boolean
    }
  }
};
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
