<template>
  <q-page class="flex-center flex q-pa-md" data-cy="vueLogin">
    <q-card style="width: 400px" square>
      <q-form @submit="handleSubmit()">
        <q-card-section class="bg-deep-purple-7 q-pa-sm">
          <h1 class="text-h5 text-white">Login</h1>
        </q-card-section>
        <q-card-section class="q-pa-lg">
          <fieldset class="q-px-sm q-pt-md q-gutter-y-lg q-pb-lg">
            <error-banner
              v-if="redirectUrl != '/dashboard'"
              v-text="$t(`auth.loginRequired`)"
            />

            <q-input
              ref="username"
              v-model="$v.email.$model"
              :error="$v.email.$error"
              :label="$t('auth.fields.email')"
              autofocus
              outlined
              data-cy="email_field"
              autocomplete="username"
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.email.$errors"
                  prefix="auth.validation.email"
                />
              </template>
            </q-input>

            <password-input
              ref="password"
              v-model="$v.password.$model"
              :error="$v.password.$error"
              :label="$t('auth.fields.password')"
              outlined
              data-cy="password_field"
              autocomplete="current-password"
              @keypress.enter="handleSubmit"
            >
              <template #hint>
                <error-field-renderer
                  :errors="$v.password.$errors"
                  prefix="auth.validation.password"
                />
              </template>
            </password-input>
          </fieldset>
          <error-banner
            v-if="error"
            :data-error="error"
            data-cy="authFailureMessages"
            v-text="$t(`auth.failures.${error}`)"
          />
        </q-card-section>
        <q-card-actions class="q-px-lg">
          <q-btn
            unelevated
            size="lg"
            color="deep-purple-7"
            class="full-width text-white"
            label="Login"
            :loading="loading"
            type="submit"
          />
        </q-card-actions>
        <q-card-section class="text-center q-pa-sm">
          <p>
            Don't have an account?
            <router-link to="/register"> Register. </router-link>
          </p>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
import PasswordInput from "src/components/forms/PasswordInput.vue"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { defineComponent, ref } from "@vue/composition-api"
import { useLogin } from "src/composables/user"

export default defineComponent({
  name: "PageLogin",
  components: { PasswordInput, ErrorFieldRenderer, ErrorBanner },
  setup(_, { root }) {
    const error = ref("")

    const { loginUser, loading, $v, redirectUrl } = useLogin()

    const handleSubmit = async () => {
      try {
        await loginUser()
        root.$router.push(redirectUrl)
      } catch (e) {
        error.value = e.message
      }
    }

    return { $v, handleSubmit, loading, error, redirectUrl }
  },
})
</script>
