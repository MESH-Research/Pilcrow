<template>
  <q-input-password
    v-bind="{ ...$props, ...$attrs }"
    :value="value"
    @input="$emit('input', $event)"
    :label="label"
    :type="isPwd ? 'password' : 'text'"
  >
    <template #append>
      <q-icon
        :name="isPwd ? 'visibility_off' : 'visibility'"
        class="cursor-pointer"
        aria-hidden="false"
        role="button"
        tabindex="0"
        :aria-pressed="(!isPwd).toString()"
        @click="isPwd = !isPwd"
        @keydown.enter.space="isPwd = !isPwd"
        :aria-label="$t('auth.aria.show_password')"
      />
    </template>
    <template v-for="(_, slot) of $scopedSlots" v-slot:[slot]="scope"
      ><slot :name="slot" v-bind="scope"
    /></template>
  </q-input-password>
</template>

<script>
import QInputPassword from "./atoms/QInputPassword.vue";

export default {
  components: { QInputPassword },
  name: "PasswordInput",
  data() {
    return {
      isPwd: true
    };
  },
  props: {
    label: {
      type: String,
      default: "Password"
    },
    value: {
      type: String
    },
    autocomplete: {
      default: "current-password"
    }
  }
};
</script>
