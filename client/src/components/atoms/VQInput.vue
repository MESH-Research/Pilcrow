<template>
  <div v-if="formState === 'loading'" :class="$attrs.class">
    <q-skeleton type="QInput" class="q-mb-md" />
  </div>
  <q-input
    v-else
    v-bind="$attrs"
    ref="input"
    v-model="model"
    :error="v.$error"
    :label="tife('label')"
    :hint="tife('hint')"
    outlined
    @clear="clearInput"
  >
    <template v-if="!$slots.error" #error>
      <error-field-renderer :errors="v.$errors" :prefix="`${tPrefix}.errors`" />
    </template>
    <template v-for="(index, name) in $slots" #[name]>
      <slot :name="name" />
    </template>
  </q-input>
</template>

<script>
import { computed, inject, ref } from "@vue/composition-api"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
export default {
  name: "VQInput",
  components: { ErrorFieldRenderer },
  props: {
    v: {
      type: Object,
      required: true,
    },
    t: {
      type: [String, Boolean],
      default: false,
    },
  },
  emits: ["vqupdate"],
  setup(props, context) {
    const parentUpdater = inject("vqupdate", null)
    const input = ref(null)
    const { root } = context
    const model = computed({
      get() {
        return props.v.$model
      },
      set(newValue) {
        const value = newValue !== null ? newValue : ""
        if (parentUpdater) {
          parentUpdater(props.v, value)
        } else {
          context.emit("vqupdate", props.v, value)
        }
      },
    })

    const parentTPrefix = inject("tPrefix", "")
    const tPrefix = computed(() => {
      if (typeof props.t === "string") {
        return props.t
      }
      return `${parentTPrefix}.${props.v.$path}`
    })

    function tife(field) {
      const key = `${tPrefix.value}.${field}`
      if (root.$te(key)) {
        return root.$t(key)
      } else {
        return null
      }
    }
    function clearInput() {
      input.value.blur()
    }
    const parentState = inject("formState", null)

    const formState = computed(() => {
      if (parentState) {
        return parentState.value
      }
      return ""
    })
    return { model, tife, context, root, tPrefix, formState, clearInput, input }
  },
}
</script>

<style lang="scss" scoped></style>
