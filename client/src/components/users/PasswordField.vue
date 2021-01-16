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
      @blur="handleBlur"
      @focus="error = false"
    >
      <template v-slot:append>
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
      <template v-slot:counter>
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
      <template v-slot:hint>
        <password-field-meter
          :max="4"
          :score="complexity.score"
          :threshold="3"
        />
        <div v-if="value.length > 0" class="password-summary">
          <span v-if="complexity.score >= 3">{{
            $t("auth.validation.password.COMPLEX")
          }}</span>
          <span v-else>{{ $t("auth.validation.password.NOT_COMPLEX") }}</span>
        </div>
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
import zxcvbn from "zxcvbn";
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
      showDetails: false,
      error: false
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
    }
  },
  computed: {
    complexity() {
      return zxcvbn(this.value);
    }
  },
  methods: {
    handleInput(event) {
      this.$emit("input", event);
    },
    handleBlur() {
      this.error = this.complexity.score < 3;
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
