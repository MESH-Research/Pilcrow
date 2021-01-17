<template>
  <div>
    <password-field-input
      v-bind="$attrs"
      :value="value"
      @input="handleInput"
      :label="label"
      :type="isPwd ? 'password' : 'text'"
      autocomplete="new-password"
      bottom-slots
      :error="error"
    >
      <template #append>
        <q-icon
          :name="isPwd ? 'visibility_off' : 'visibility'"
          class="cursor-pointer"
          aria-hidden="false"
          role="button"
          tabindex="0"
          :aria-pressed="isPwd.toString()"
          @click="isPwd = !isPwd"
          @keydown.enter.space="isPwd = !isPwd"
          :aria-label="$t('auth.aria.show_password')"
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
          @click="showDetails = !showDetails"
          @keydown.enter.space="showDetails = !showDetails"
          >{{ $t("buttons.more_info") }}</q-chip
        >
      </template>
      <template #hint>
        <password-field-meter :max="4" :score="score" :valid="!error" />
        <div v-if="value.length > 0" class="password-summary">
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
    </password-field-input>
    <div v-if="showDetails" class="password-details alert alert-tip">
      <div class="alert-title">{{ $t("auth.password_meter.header") }}</div>
      <password-field-analysis
        id="password-field-analysis"
        :complexity="complexity"
        class="alert-body"
      />
    </div>
  </div>
</template>

<script>
import PasswordFieldAnalysis from "./PasswordFieldAnalysis.vue";
import PasswordFieldInput from "./PasswordFieldInput.vue";
import PasswordFieldMeter from "./PasswordFieldMeter.vue";

export default {
  components: { PasswordFieldMeter, PasswordFieldAnalysis, PasswordFieldInput },
  name: "PasswordField",
  inheritAttrs: false,
  data() {
    return {
      isPwd: true,
      showDetails: false
    };
  },
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
      type: Object
    },
    error: {
      type: Boolean
    }
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
  padding-bottom: 40px;

.password-details
  font-size: 12px;

.q-chip
  margin: 0;

.password-warning
  font-weight: bold;
</style>
