<template>
  <q-page class="verify-page">
    <q-card style="width: 600px">
      <q-card-section horizontal>
        <q-card-section
          class="card-icon flex-center flex"
          :class="status == 'failure' ? 'error' : ''"
        >
          <q-spinner-hourglass
            v-if="status == 'loading'"
            size="3em"
          />
          <q-icon
            v-else-if="status == 'success'"
            name="email"
          />
          <q-icon
            v-else
            name="error"
          />
        </q-card-section>
        <q-card-section class="col-grow q-pb-none">
          <div v-if="status == 'loading'">
            Loading...
          </div>
          <div v-else>
            <div v-if="status == 'success'">
              {{ $t("account.email_verify.verification_success") }}
            </div>
            <div v-else>
              {{ $t("general_failure") }}
              <ul class="errors">
                <li
                  v-for="(message, index) in errorMessages"
                  :key="index"
                >
                  {{ message }}
                </li>
              </ul>
            </div>
          </div>
          <q-card-actions align="right">
            <email-verification-send-button
              v-if="status == 'failure'"
              flat
              no-color
            />
            <q-btn
              flat
              to="/dashboard"
            >
              <q-icon name="arrow_forward" />
              {{ $t("buttons.dashboard") }}
            </q-btn>
          </q-card-actions>
        </q-card-section>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import { VERIFY_EMAIL } from "src/graphql/mutations";
import { CURRENT_USER } from "src/graphql/queries";
import EmailVerificationSendButton from "src/components/atoms/EmailVerificationSendButton.vue";
import errorMixin from "src/components/mixins/errors";

export default {
  name: "VerifyEmail",
  components: { EmailVerificationSendButton },
  mixins: [errorMixin],
  data() {
    return {
      status: "loading",
      errorMessages: []
    };
  },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  },
  async created() {
    const { token, expires } = this.$route.params;

    if (this.currentUser.email_verified_at) {
      this.status = "success";
      return;
    }
    try {
      await this.$apollo.mutate({
        mutation: VERIFY_EMAIL,
        variables: { token, expires },
        update: (
          store,
          {
            data: {
              verifyEmail: { email_verified_at }
            }
          }
        ) => {
          const { currentUser } = store.readQuery({
            query: CURRENT_USER
          });
          currentUser.email_verified_at = email_verified_at;
          store.writeQuery({
            query: CURRENT_USER,
            data: { currentUser }
          });
        }
      });
      this.status = "success";
    } catch (error) {
      this.errorMessages = this.$errorMessages(
        this.$graphQLErrorCodes(error),
        "account.failures"
      );
      this.status = "failure";
    }
  }
};
</script>

<style lang="scss">
.verify-page {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 3em;

  .card-icon {
    &.error {
      background-color: $red;
    }
    background-color: $primary;
    color: white;
    .q-icon {
      font-size: 3em;
    }
  }
}
</style>
