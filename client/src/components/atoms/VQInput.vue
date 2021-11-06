<template>
  <div v-if="formState === 'loading'" :class="$attrs.class">
    <q-skeleton type="QInput" class="q-mb-md" />
  </div>
  <q-input
    v-else
    v-model="model"
    :error="v.$error"
    :label="tife('label')"
    v-bind="$attrs"
    outlined
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
import { computed, inject } from "@vue/composition-api"
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
    const { root } = context
    const model = computed({
      get() {
        return props.v.$model
      },
      set(newValue) {
        if (parentUpdater) {
          parentUpdater(props.v, newValue)
        } else {
          context.emit("vqupdate", props.v, newValue)
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

    const parentState = inject("formState", null)

    const formState = computed(() => {
      if (parentState) {
        return parentState.value
      }
      return ""
    })
    return { model, tife, context, root, tPrefix, formState }
  },
}
</script>

<style lang="scss" scoped></style>
