<template>
  <div>
    <password-input
      v-bind="{ ...$props, ...$attrs }"
      :model-value="$props.modelValue"
      :label="label"
      autocomplete="new-password"
      :error="error"
      @update:model-value="$emit('update:modelValue', $event)"
    >
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="{ ...slotData }" />
      </template>
    </password-input>
    <div class="row items-center">
      <new-password-input-meter
        :max="4"
        :score="score"
        :valid="!error"
        class="col"
      />
      <q-chip
        icon="help"
        dense
        size="md"
        clickable
        :aria-label="$t('auth.aria.more_info_password')"
        aria-controls="password-field-analysis"
        :aria-expanded="showDetails.toString()"
        tabindex="0"
        role="button"
        outline
        @click="showDetails = !showDetails"
        @keydown.enter.space="showDetails = !showDetails"
      >
        {{ $t("buttons.more_info") }}
      </q-chip>
    </div>
    <div v-if="$props.modelValue.length > 0" class="password-summary">
      <span v-if="!error">{{ $t("auth.validation.PASSWORD_COMPLEX") }}</span>
    </div>
    <div
      v-else-if="$props.modelValue.length == 0 && error"
      v-text="$t('helpers.REQUIRED_FIELD', [[$t('auth.fields.password')]])"
    />
    <div
      v-if="showDetails"
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

<script lang="ts">
import { defineComponent } from "vue"
export default defineComponent({
  inheritAttrs: false
})
</script>

<script setup lang="ts">
import type { ZxcvbnResult } from "@zxcvbn-ts/core"
import NewPasswordInputAnalysis from "./atoms/NewPasswordInputAnalysis.vue"
import PasswordInput from "./PasswordInput.vue"
import NewPasswordInputMeter from "./atoms/NewPasswordInputMeter.vue"

interface Props {
  label?: string
  modelValue?: string
  threshold?: number
  complexity?: ZxcvbnResult
  error: boolean
}
const props = withDefaults(defineProps<Props>(), {
  label: "Password",
  modelValue: "",
  threshold: 3,
  complexity: null
})

defineEmits(["update:modelValue"])

const showDetails = ref(false)

const score = computed(() => {
  return props.complexity.score
})
</script>

<style lang="sass" scoped>
.q-field--with-bottom
  padding-bottom: 40px

.q-chip
  margin: 0

.password-warning
  font-weight: bold
</style>
