<template>
  <component :is="component" :meta-control="metaControl" />
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from "vue"
import { camelCase } from "lodash"

const { metaControl } = defineProps({
  metaControl: {
    type: Object,
    required: true
  }
})

const component = computed(() => {
  const name = camelCase(metaControl.type).replace(/^./, (c) => c.toUpperCase())
  return defineAsyncComponent(() => import(`./controls/${name}MetaControl.vue`))
})
</script>

<script lang="ts">
import { gql } from "graphql-tag"

export const META_CONTROLS_FRAGMENT = gql`
  fragment MetaControls on MetaForm {
    meta_prompts {
      id
      type
      label
      options
    }
  }
`
</script>
