<template>
  <q-btn
    :loading="status == 'loading'"
    :color="btnColor"
    class="text-black"
    v-bind="{ ...$props, ...$attrs }"
    @click="send"
  >
    <template v-if="status == 'success'">
      <q-icon name="check" />
      {{ $t("account.email_verify.resend_button_success") }}
    </template>
    <template v-else>
      {{ $t("account.email_verify.resend_button") }}
    </template>
    <template #loading>
      <q-spinner-hourglass class="on-left" />
      {{ $t("account.email_verify.resend_button_loading") }}
    </template>
  </q-btn>
</template>

<script>
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations";
import errorsMixin from "src/components/mixins/errors";

export default {
  name: "EmailVerificationSendButton",
  mixins: [errorsMixin],
  props: {
    noColor: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      status: null
    };
  },
  computed: {
    btnColor() {
      if (this.noColor) {
        return null;
      }
      if (this.status == "success") {
        return "positive";
      }
      return null;
    }
  },
  methods: {
    async send() {
      this.status = "loading";
      try {
        const {
          data: {
            sendEmailVerification: { email }
          }
        } = await this.$apollo.mutate({
          mutation: SEND_VERIFY_EMAIL
        });
        this.status = "success";
        this.$q.notify({
          color: "positive",
          message: this.$t("account.email_verify.send_success_notify", {
            email
          }),
          icon: "email",
          html: true
        });
      } catch (error) {
        const errorMessages = this.$errorMessages(
          this.$graphQLErrorCodes(error),
          "account.failures"
        );
        if (!errorMessages.length) {
          errorMessages.push(this.$t("failures.UNKNOWN_ERROR"));
        }
        this.$q.notify({
          color: "negative",
          message: this.$t("account.email_verify.send_failure_notify", {
            errors: errorMessages.join(", ")
          }),
          icon: "error"
        });
        this.status = null;
      }
    }
  }
};
</script>
