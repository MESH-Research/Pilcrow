<template>
  <div>
    <password-input
      v-bind="{ ...$props, ...$attrs }"
      :value="value"
      :label="label"
      autocomplete="new-password"
      bottom-slots
      :error="error"
      @input="$emit('input', $event)"
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
          @click="showDetails = !showDetails"
          @keydown.enter.space="showDetails = !showDetails"
        >
          {{ $t("buttons.more_info") }}
        </q-chip>
      </template>
      <template #hint>
        <new-password-input-meter
          :max="4"
          :score="score"
          :valid="!error"
        />
        <div
          v-if="value.length > 0"
          class="password-summary"
        >
          <span v-if="!error">{{
            $t("auth.validation.PASSWORD_COMPLEX")
          }}</span>
          <span v-else>{{ $t("auth.validation.PASSWORD_NOT_COMPLEX") }}</span>
        </div>
        <div
          v-else-if="value.length == 0 && error"
          v-text="$t('helpers.REQUIRED_FIELD', ['Password'])"
        />
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
import NewPasswordInputAnalysis from "./atoms/NewPasswordInputAnalysis.vue";
import PasswordInput from "./PasswordInput.vue";
import NewPasswordInputMeter from "./atoms/NewPasswordInputMeter.vue";

export default {
  name: "NewPasswordInput",
  components: {
    NewPasswordInputAnalysis,
    PasswordInput,
    NewPasswordInputMeter
  },
  inheritAttrs: false,
  props: {
    label: {
      type: String,
      default: "Password"
    },
    value: {
      type: String,
      default: ""
    },
    threshold: {
      type: Number,
      default: 3
    },
    complexity: {
      type: Object,
      required: true
    },
    error: {
      type: Boolean
    }
  },
  data() {
    return {
      isPwd: true,
      showDetails: false
    };
  },
  computed: {
    score() {
      return this.complexity.score;
    }
  },
  methods: {
    handleInput(event) {
      this.$emit("input", event);
    }
  }
};
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
