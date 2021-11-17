<template>
  <q-page-sticky v-if="visible" position="bottom-right">
    <div class="bg-grey-1 q-ma-sm q-pa-md rounded-borders shadow-15">
      <div class="q-gutter-md">
        <template v-if="$slots.default">
          <slot />
        </template>
        <template v-else>
          <q-btn
            :disabled="saveButton.disabled"
            :class="saveButton.class"
            :data-cy="saveButton.cyAttr"
            type="submit"
          >
            <q-icon v-if="saveButton.icon === 'check'" name="check" />
            <q-spinner v-else-if="saveButton.icon === 'spinner'" />
            {{ $t(saveButton.text) }}
          </q-btn>
          <q-btn
            v-if="!resetBtn.disabled"
            class="bg-grey-4 ml-sm"
            data-cy="button_discard"
            @click="$emit('resetClick')"
          >
            {{ $t("buttons.discard_changes") }}
          </q-btn>
        </template>
      </div>
    </div>
  </q-page-sticky>
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
      cyAttr: computed(() => {
        return (
          {
            saving: "button_saving",
            saved: "button_saved",
          }[props.formState] ?? "button_save"
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
            saved: false,
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

    const visible = computed(() => {
      return (
        {
          idle: false,
          loading: false,
        }[props.formState] ?? true
      )
    })

    return { saveButton, resetBtn, visible }
  },
})
</script>
