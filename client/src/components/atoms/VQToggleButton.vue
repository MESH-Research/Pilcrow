<template>
  <div v-if="formState === 'loading'" :class="$attrs.class">
    <q-skeleton type="QInput" class="q-mb-md" />
  </div>
  <div v-else>
    <div class="row items-center justify-start q-gutter-x-md">
      <div>
        <q-btn-toggle
          v-bind="$attrs"
          ref="toggle"
          v-model="model"
          toggle-color="primary"
          :options="[
            { label: getTranslation('options.true'), value: true },
            { label: getTranslation('options.false'), value: false },
          ]"
        />
      </div>
      <div
        v-if="getTranslation('effect.true') || getTranslation('effect.false')"
      >
        <span v-if="model">{{ getTranslation("effect.true") }}</span>
        <span v-else>{{ getTranslation("effect.false") }} </span>
      </div>
    </div>
    <div
      v-if="getTranslation('hint')"
      class="q-field__bottom row items-start q-field__bottom--animated"
    >
      <div class="q-field__messages col">
        {{ getTranslation("hint") }}
      </div>
      <!---->
    </div>
  </div>
</template>

<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import { inject } from "vue"
import { useVQWrap } from "src/use/forms"
/**
 * Transparent wrapper for q-input that handles validation and translation by convention.
 *
 * @see https://v1.quasar.dev/vue-components/input#qinput-api
 */

const props = defineProps({
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
})
defineEmits(["vqupdate"])

const { getTranslation, model } = useVQWrap(props.v, props.t)

const { state: formState = "" } = inject("formState", {})
</script>

<style lang="scss" scoped></style>
