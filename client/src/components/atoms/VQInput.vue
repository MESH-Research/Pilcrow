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
    :label="getTranslation('label')"
    :hint="te(getTranslationKey('hint')) ? getTranslation('hint') : null"
    outlined
    @clear="clearInput"
  >
    <template v-if="!$slots.error" #error>
      <error-field-renderer
        :errors="v.$errors"
        :prefix="getTranslationKey('errors')"
      />
    </template>
    <template v-for="(_, name) in $slots" #[name]>
      <slot :name="name" />
    </template>
  </q-input>
</template>

<script lang="ts">
export default {
  inheritAttrs: false
}
</script>
<script setup lang="ts">
import { inject, ref } from "vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { useVQWrap } from "src/use/forms"
import { useI18n } from "vue-i18n"
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
    required: true
  },
  /**
   * Translation key for label, hint and error messages.
   * VQWrap can also provide a tPrefix, allowing the component to use validation path to compute translation key.
   *
   * @see src/components/atoms/VQWrap.vue
   */
  t: {
    type: [String, Boolean],
    default: false
  }
})
defineEmits(["vqupdate"])

const input = ref(null)

const { getTranslation, getTranslationKey, model } = useVQWrap(props.v, props.t)

const { te } = useI18n()

function clearInput() {
  input.value.blur()
}
const { state: formState = "" } = inject("formState", {})
</script>

<style lang="scss" scoped></style>
