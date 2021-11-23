<template>
  <div v-if="formState === 'loading'" :class="$attrs.class">
    <q-skeleton type="QInput" class="q-mb-md" />
  </div>
  <q-input
    v-else
    v-bind="$attrs"
    ref="input"
    v-model="model"
    :data-cy="cyAttr"
    :error="v.$error"
    :label="$te(fullTKey('label')) ? $t(fullTKey('label')) : null"
    :hint="$te(fullTKey('hint')) ? $t(fullTKey('hint')) : null"
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

/**
 * Transparent wrapper for q-input that handles validation and translation by convention.
 *
 * @see https://v1.quasar.dev/vue-components/input#qinput-api
 */
export default {
  name: "VQInput",
  components: { ErrorFieldRenderer },
  props: {
    cyAttr: {
      type: String,
      default: "",
    },
    /**
     * Vuelidate validator object the input should use.
     */
    v: {
      type: Object,
      required: true,
    },
    /**
     * Translation key for label, hint and error messages.
     * VQWrap can also provide a tPrefix, allowing the component to use validation path to compute translation key.
     *
     * @see src/components/atoms/VQWrap.vue
     */
    t: {
      type: [String, Boolean],
      default: false,
    },
  },
  emits: ["vqupdate"],
  setup(props, { emit }) {
    const parentUpdater = inject("vqupdate", null)
    const input = ref(null)
    const model = computed({
      get() {
        return props.v.$model
      },
      set(newValue) {
        const value = newValue !== null ? newValue : ""
        if (parentUpdater) {
          parentUpdater(props.v, value)
        } else {
          /**
           * Emits any update to the underlying input.  Parent component is responsible for updateing the validation model.
           *
           * @param Object validator
           * @param value New value for input
           * @event vqupdate
           * @see src/components/atoms/VQWrap.vue
           */
          emit("vqupdate", props.v, value)
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

    /**
     * Provide full translation key for a field.
     */
    const fullTKey = (key) => {
      return `${tPrefix.value}.${key}`
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
    return {
      model,
      tPrefix,
      fullTKey,
      formState,
      clearInput,
      input,
    }
  },
}
</script>

<style lang="scss" scoped></style>
