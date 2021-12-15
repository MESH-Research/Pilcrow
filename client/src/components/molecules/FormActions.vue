<template>
  <q-page-sticky v-show="visible" position="bottom-right">
    <div class="bg-grey-1 q-ma-sm q-pa-md rounded-borders shadow-15">
      <div class="q-gutter-md">
        <template v-if="$slots.default">
          <slot />
        </template>
        <template v-else>
          <q-btn
            :disabled="saveDisabled"
            :class="saveClassList"
            :data-cy="saveCyAttr"
            type="submit"
          >
            <q-icon v-if="saveIcon === 'check'" name="check" />
            <q-spinner v-else-if="saveIcon === 'spinner'" />
            {{ $t(saveText) }}
          </q-btn>
          <q-btn
            v-if="!resetDisabled"
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
import { computed } from "@vue/composition-api"

export default {
  name: "FormActions",
  props: {
    formState: {
      type: String,
      default: "idle",
    },
  },
  setup(props) {
    const saveClassList = computed(() => {
      const classes = {
        saved: "bg-positive text-white",
        dirty: "bg-primary text-white",
      }
      return classes[props.formState] ?? "bg-grey-3"
    })
    const saveCyAttr = computed(() => {
      return (
        {
          saving: "button_saving",
          saved: "button_saved",
        }[props.formState] ?? "button_save"
      )
    })
    const saveText = computed(() => {
      return (
        {
          saving: "buttons.saving",
          saved: "buttons.saved",
        }[props.formState] ?? "buttons.save"
      )
    })
    const saveDisabled = computed(() => {
      return (
        {
          saved: false,
          dirty: false,
        }[props.formState] ?? true
      )
    })
    const saveIcon = computed(() => {
      return (
        {
          saved: "check",
          saving: "spinner",
        }[props.formState] ?? ""
      )
    })

    const resetDisabled = computed(() => {
      return (
        {
          dirty: false,
        }[props.formState] ?? true
      )
    })

    const visible = computed(() => {
      return (
        {
          idle: false,
          loading: false,
        }[props.formState] ?? true
      )
    })

    return {
      saveClassList,
      saveCyAttr,
      saveText,
      saveDisabled,
      saveIcon,
      resetDisabled,
      visible,
    }
  },
}
</script>
