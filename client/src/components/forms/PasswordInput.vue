<template>
  <q-input-password
    v-bind="{ ...$props, ...$attrs }"
    :value="value"
    :label="label"
    :type="isPwd ? 'password' : 'text'"
    @input="$emit('input', $event)"
  >
    <template #append>
      <q-icon
        :name="isPwd ? 'visibility_off' : 'visibility'"
        class="cursor-pointer"
        aria-hidden="false"
        role="button"
        tabindex="0"
        :aria-pressed="(!isPwd).toString()"
        :aria-label="$t('auth.aria.show_password')"
        @click="isPwd = !isPwd"
        @keydown.enter.space="isPwd = !isPwd"
      />
    </template>
    <template
      v-for="(_, slot) of $scopedSlots"
      #[slot]="scope"
    >
      <slot
        :name="slot"
        v-bind="scope"
      />
    </template>
  </q-input-password>
</template>

<script>
import QInputPassword from "./atoms/QInputPassword.vue";

export default {
  name: "PasswordInput",
  components: { QInputPassword },
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
    autocomplete: {
      default: "current-password",
      type: String
    },
    outlined: {
      type: Boolean,
      default: false
    },
    error: {
      type: [String, Boolean],
      default: false
    }
  },
  data() {
    return {
      isPwd: true
    };
  }
};
</script>
