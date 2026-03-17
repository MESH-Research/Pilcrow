<template>
  <ul>
    <li v-for="error in errors" :key="error.$validator">
      {{ $t(`${prefix}.${getErrorMessageKey(error)}`) }}
    </li>
  </ul>
</template>

<script setup lang="ts">
import { getErrorMessageKey } from "src/use/validationHelpers"
import type { ErrorObject } from "@vuelidate/core"

interface Props {
  errors: ErrorObject[]
  prefix?: string
}
withDefaults(defineProps<Props>(), {
  prefix: ""
})
</script>

<style scoped>
li {
  display: block;
}
ul {
  margin-block: 0;
  padding-inline: 0;
}
</style>
