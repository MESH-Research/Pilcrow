<template>
  <component
    :is="sticky ? 'q-page-sticky' : 'div'"
    v-if="visible"
    position="bottom-right"
  >
    <div
      class="form-actions"
      :class="sticky ? 'q-ma-sm q-pa-md rounded-borders shadow-15' : ''"
    >
      <div class="q-gutter-md">
        <template v-if="$slots.default">
          <slot />
        </template>
        <template v-else>
          <q-banner
            v-if="state === 'error'"
            class="text-white bg-negative"
            dense
          >
            {{ errorMessage }}
          </q-banner>
          <q-btn
            :disabled="saveDisabled"
            :class="saveClassList"
            :data-cy="saveCyAttr"
            type="submit"
            :flat="flat"
          >
            <q-icon v-if="saveIcon === 'check'" name="check" />
            <q-spinner v-else-if="saveIcon === 'spinner'" />
            {{ $t(saveText) }}
          </q-btn>
          <q-btn
            v-if="!resetDisabled"
            class="light-grey ml-sm"
            data-cy="button_discard"
            :flat="flat"
            @click="$emit('resetClick')"
          >
            {{ $t("buttons.discard_changes") }}
          </q-btn>
        </template>
      </div>
    </div>
  </component>
</template>

<script setup>
import { computed, inject } from "vue"

const { state, errorMessage } = inject("formState")

defineEmits(["resetClick"])

const props = defineProps({
  sticky: {
    type: Boolean,
    default: true,
  },
  flat: {
    type: Boolean,
    default: false,
  },
})

const saveClassList = computed(() => {
  const classes = {
    saved: "bg-positive text-white",
    dirty: "bg-accent text-white",
  }
  return classes[state.value] ?? "bg-grey-8 text-white"
})
const saveCyAttr = computed(() => {
  return (
    {
      saving: "button_saving",
      saved: "button_saved",
    }[state.value] ?? "button_save"
  )
})
const saveText = computed(() => {
  return (
    {
      saving: "buttons.saving",
      saved: "buttons.saved",
    }[state.value] ?? "buttons.save"
  )
})
const saveDisabled = computed(() => {
  return (
    {
      saved: false,
      dirty: false,
      error: false,
    }[state.value] ?? true
  )
})
const saveIcon = computed(() => {
  return (
    {
      saved: "check",
      saving: "spinner",
    }[state.value] ?? ""
  )
})

const resetDisabled = computed(() => {
  return (
    {
      dirty: false,
      error: false,
    }[state.value] ?? true
  )
})

const visible = computed(() => {
  if (!props.sticky) return true

  return (
    {
      idle: false,
      loading: false,
    }[state.value] ?? true
  )
})
</script>
