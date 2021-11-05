<template>
  <q-input
    v-model="model"
    :error="v.$error"
    :label="tife('label')"
    :hint="tife('hint')"
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
import { computed, onMounted, ref } from "@vue/composition-api"
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
    const { root } = context
    const model = computed({
      get() {
        return props.v.$model
      },
      set(newValue) {
        if (eventNode.value) {
          eventNode.value.$emit("vqupdate", props.v, newValue)
        }
      },
    })

    function getFirstParentWith(target, type) {
      let parent = context.parent
      console.log(parent[type]?.[target])
      while (parent && !parent[type]?.[target]) {
        if (parent.$parent) {
          parent = parent.$parent
        } else {
          return null
        }
      }
      return parent
    }

    const eventNode = ref(null)
    const tNode = ref(null)

    onMounted(() => {
      eventNode.value = getFirstParentWith("vqupdate", "$listeners")
      tNode.value = getFirstParentWith("t", "$attrs")
    })

    const tPrefix = computed(() => {
      if (typeof props.t === "string") {
        return props.t
      }
      if (tNode.value) return `${tNode.value.$attrs.t}.${props.v.$path}`
      return null
    })

    function tife(field) {
      const key = `${tPrefix.value}.${field}`
      if (root.$te(key)) {
        return root.$t(key)
      } else {
        return null
      }
    }
    return { model, tife, context, root, tPrefix }
  },
}
</script>

<style lang="scss" scoped></style>
