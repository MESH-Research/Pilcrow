<template>
  <div class="q-mb-lg">
    <form-section-header v-if="$slots.header"
      ><slot name="header"></slot
    ></form-section-header>
    <q-card-section class="q-col-gutter-y-lg">
      <slot></slot>
    </q-card-section>
  </div>
</template>

<script>
import { defineComponent } from "@vue/composition-api"
import FormSectionHeader from "../atoms/FormSectionHeader.vue"

export default defineComponent({
  name: "FormSection",
  components: { FormSectionHeader },
})
</script>
