<template>
  <q-page class="flex-center flex q-pa-md" data-cy="vueLogin">
    <q-card style="width: 400px" square>
      <q-form @submit="handleSubmit()">
        <q-card-section class="accent q-pa-sm">
          <h1 class="text-h5 text-white">{{ $t(`auth.login`) }}</h1>
        </q-card-section>
        <q-card-section class="q-pa-lg">
          <fieldset class="q-px-sm q-pt-md q-gutter-y-lg q-pb-lg">
            <error-banner v-if="redirectUrl != '/dashboard'">
              {{ $t(`auth.loginRequired`) }}
            </error-banner>

            <q-input
              ref="username"
              v-model="v$.email.$model"
              :error="v$.email.$error"
              :label="$t('auth.fields.email')"
              autofocus
              outlined
              data-cy="email_field"
              autocomplete="username"
            >
              <template #error>
                <error-field-renderer
                  :errors="v$.email.$errors"
                  prefix="auth.validation.email"
                />
              </template>
            </q-input>

            <password-input
              ref="password"
              v-model="v$.password.$model"
              :error="v$.password.$error"
              :label="$t('auth.fields.password')"
              outlined
              data-cy="password_field"
              autocomplete="current-password"
              @keypress.enter="handleSubmit"
            >
              <template #hint>
                <error-field-renderer
                  :errors="v$.password.$errors"
                  prefix="auth.validation.password"
                />
              </template>
            </password-input>
          </fieldset>
          <error-banner
            v-if="error"
            :data-error="error"
            data-cy="authFailureMessages"
          >
            {{ $t(`auth.failures.${error}`) }}
          </error-banner>
        </q-card-section>
        <q-card-actions class="q-px-lg">
          <q-btn
            id="submitBtn"
            ref="submitBtn"
            unelevated
            size="lg"
            color="accent"
            class="full-width text-white"
            :label="$t(`auth.login`)"
            :loading="loading"
            type="submit"
          />
        </q-card-actions>
        <q-card-section class="text-center q-pa-sm">
          <p>
            {{ $t("auth.register_question") }}
            <router-link to="/register" class="dark-accent-text">
              {{ $t("auth.register") }}
            </router-link>
          </p>
          <p>
            {{ $t("auth.password_forgot") }}
            <router-link to="/request-password-reset" class="dark-accent-text">
              {{ $t("auth.password_reset") }}
            </router-link>
          </p>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup>
import PasswordInput from "src/components/forms/PasswordInput.vue"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { ref } from "vue"
import { useLogin } from "src/use/user"
import { useRouter } from "vue-router"

const error = ref("")

const { loginUser, loading, v$, redirectUrl } = useLogin()
const { push } = useRouter()
const handleSubmit = async () => {
  try {
    await loginUser()
    push(redirectUrl)
  } catch (e) {
    error.value = e.message
  }
}
</script>
