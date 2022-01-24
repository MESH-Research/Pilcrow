<template>
  <div>
    <password-input
      v-bind="{ ...$props, ...$attrs }"
      :model-value="value"
      :label="label"
      autocomplete="new-password"
      bottom-slots
      :error="error"
      @update:model-value="$emit('update:modelValue', $event)"
    >
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
          @click="showDetails = !showDetails"
          @keydown.enter.space="showDetails = !showDetails"
        >
          {{ $t("buttons.more_info") }}
        </q-chip>
      </template>
      <template #hint>
        <new-password-input-meter :max="4" :score="score" :valid="!error" />
        <div v-if="value.length > 0" class="password-summary">
          <span v-if="!error">{{
            $t("auth.validation.PASSWORD_COMPLEX")
          }}</span>
          <span v-else>{{ $t("auth.validation.PASSWORD_NOT_COMPLEX") }}</span>
        </div>
        <div
          v-else-if="value.length == 0 && error"
          v-text="$t('helpers.REQUIRED_FIELD', [[$t('auth.fields.password')]])"
        />
      </template>
      <template v-for="(_, name) in $slots" #[name]="slotData">
        <slot :name="name" v-bind="{ ...slotData }" />
      </template>
    </password-input>
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
import { computed, ref } from "vue"
const props = defineProps({
  label: {
    type: String,
    default: "Password",
  },
  value: {
    type: String,
    default: "",
  },
  threshold: {
    type: Number,
    default: 3,
  },
  complexity: {
    type: Object,
    required: true,
  },
  error: {
    type: Boolean,
  },
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

.password-details
  font-size: 12px

.q-chip
  margin: 0

.password-warning
  font-weight: bold
</style>
