<template>
  <q-card-section
    class="bg-secondary text-white q-mx-md"
    :class="{ 'q-py-sm': !firstSection }"
  >
    <div :class="headerClass"><slot></slot></div>
  </q-card-section>
</template>

<script>
import { defineComponent, computed } from "@vue/composition-api"

export default defineComponent({
  name: "FormSectionHeader",
  props: {
    firstSection: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const headerClass = computed(() => {
      return props.firstSection ? "text-h4" : "text-subtitle2"
    })

    return { headerClass }
  },
})
</script>
