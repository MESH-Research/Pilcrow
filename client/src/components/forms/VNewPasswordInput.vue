<template>
  <div>
    <password-input
      v-model="model"
      v-bind="{ ...$attrs }"
      outlined
      :label="$t(getTranslationKey('label'))"
      :hint="$t(getTranslationKey('hint'))"
      autocomplete="new-password"
      :error="v.$error"
    >
      <template v-if="!$slots.error" #error>
        <error-field-renderer
          :errors="v.$errors"
          :prefix="$t(getTranslationKey('errors'))"
        />
      </template>
      <template #counter>
        <q-chip
          icon="help"
          dense
          size="sm"
          clickable
          :aria-label="$t('auth.aria.more_info_password')"
          aria-controls="password-field-analysis"
          :aria-expanded="showDetails.toString()"
          tabindex="0"
          role="button"
          outline
          class="col-3"
          @click="showDetails = !showDetails"
          @keydown.enter.space="showDetails = !showDetails"
        >
          {{ $t("buttons.more_info") }}
        </q-chip>
      </template>
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="{ ...slotData }" />
      </template>
    </password-input>
    <div class="row items-center" style="max-width: 400px">
      <new-password-input-meter
        :max="4"
        :score="score"
        :valid="!v.$error"
        class="col"
      />
    </div>
    <div
      v-if="showDetails && model.length"
      id="password-field-analysis"
      class="password-details alert alert-tip"
    >
      <div class="alert-title">
        {{ $t("auth.password_meter.header") }}
      </div>
      <new-password-input-analysis
        :complexity="complexity"
        class="alert-body"
      />
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue"
export default defineComponent({
  inheritAttrs: false,
})
</script>

<script setup>
import NewPasswordInputAnalysis from "./atoms/NewPasswordInputAnalysis.vue"
import PasswordInput from "./PasswordInput.vue"
import NewPasswordInputMeter from "./atoms/NewPasswordInputMeter.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { computed, ref } from "vue"
import { useVQWrap } from "src/use/forms"

const props = defineProps({
  v: {
    type: Object,
    required: true,
  },
  t: {
    type: [String, Boolean],
    default: false,
  },
})

defineEmits(["vqupdate"])

const { getTranslationKey, model } = useVQWrap(props.v, props.t)
const showDetails = ref(false)

const complexity = computed(() => {
  return props.v.notComplex.$response.complexity
})

const score = computed(() => {
  return complexity.value.score
})
</script>

<style lang="sass" scoped>
.q-field--with-bottom
  padding-bottom: 40px

.password-details
  font-size: 12px

.q-chip
  margin: 0

.password-warning
  font-weight: bold
</style>
