<template>
  <q-card-section class="bg-grey-2 row justify-end q-ma-md">
    <div class="q-gutter-md">
      <template v-if="$slots.default">
        <slot />
      </template>
      <template v-else>
        <q-btn
          :disabled="saveButton.disabled"
          :class="saveButton.class"
          data-cy="button_save"
          type="submit"
        >
          <q-icon v-if="saveButton.icon === 'check'" name="check" />
          <q-spinner v-else-if="saveButton.icon === 'spinner'" />
          {{ $t(saveButton.text) }}
        </q-btn>
        <q-btn
          :disabled="resetBtn.disabled"
          class="bg-grey-4 ml-sm"
          data-cy="button_discard"
          @click="$emit('resetClick')"
        >
          {{ $t("buttons.discard_changes") }}
        </q-btn>
      </template>
    </div>
  </q-card-section>
</template>

<script>
import { defineComponent, computed, reactive } from "@vue/composition-api"

export default defineComponent({
  name: "FormActions",
  props: {
    formState: {
      type: String,
      default: "idle",
    },
  },
  setup(props) {
    const saveButton = reactive({
      class: computed(() => {
        return (
          {
            saved: "bg-positive text-white",
            dirty: "bg-primary text-white",
          }[props.formState] ?? "bg-grey-3"
        )
      }),

      text: computed(() => {
        return (
          {
            saving: "buttons.saving",
            saved: "buttons.saved",
          }[props.formState] ?? "buttons.save"
        )
      }),
      disabled: computed(() => {
        return (
          {
            dirty: false,
          }[props.formState] ?? true
        )
      }),
      icon: computed(() => {
        return (
          {
            saved: "check",
            saving: "spinner",
          }[props.formState] ?? ""
        )
      }),
    })

    const resetBtn = reactive({
      disabled: computed(() => {
        return (
          {
            dirty: false,
          }[props.formState] ?? true
        )
      }),
    })

    return { saveButton, resetBtn }
  },
})
</script>
