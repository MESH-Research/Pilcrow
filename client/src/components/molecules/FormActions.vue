<template>
  <q-card-section class="bg-grey-2 row justify-end q-ma-md">
    <div class="q-gutter-md">
      <slot></slot>
    </div>
  </q-card-section>
</template>

<script>
import { defineComponent } from "@vue/composition-api"

export default defineComponent({
  name: "FormActions",
})
</script>
