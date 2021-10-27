<template>
  <q-card-section class="bg-secondary text-white q-mx-md">
    <div class="text-subtitle2"><slot></slot></div>
  </q-card-section>
</template>

<script>
import { defineComponent } from "@vue/composition-api"

export default defineComponent({
  name: "FormSectionHeader",
})
</script>
