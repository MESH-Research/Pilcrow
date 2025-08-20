<template>
  <q-page class="flex-center flex q-pa-md">
    <div v-if="loadingProviders">
      <q-spinner color="primary" />
    </div>
    <section v-else style="width: 400px" class="q-px-lg q-mx-lg">
      <q-card square class="dark-mode-only-card">
        <q-form @submit="handleSubmit()">
          <q-card-section class="bg-primary">
            <h1 class="text-h4 text-white q-my-xs">{{ $t(`auth.login`) }}</h1>
          </q-card-section>
          <q-card-section>
            <fieldset class="q-gutter-y-lg q-pt-lg">
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
            <div class="q-px-md">
              <error-banner
                v-if="error"
                :data-error="error"
                data-cy="authFailureMessages"
              >
                {{ $t(`auth.failures.${error}`) }}
              </error-banner>
            </div>
          </q-card-section>
          <q-card-actions class="q-px-lg q-pb-lg">
            <q-btn
              id="submitBtn"
              ref="submitBtn"
              unelevated
              size="lg"
              color="primary"
              class="full-width text-white"
              :label="$t(`auth.log_in`)"
              :loading="loading"
              type="submit"
            />
          </q-card-actions>
        </q-form>
        <q-card-section class="text-center q-pa-sm">
          <p>
            {{ $t("auth.register_question") }}
            <router-link to="/register" class="dark-accent-text">
              {{ $t("auth.register_link") }}
            </router-link>
          </p>
          <p>
            {{ $t("auth.password_forgot") }}
            <router-link to="/request-password-reset" class="dark-accent-text">
              {{ $t("auth.password_reset") }}
            </router-link>
          </p>
        </q-card-section>
      </q-card>
    </section>
    <section v-if="providers.length > 0" style="width: 400px" class="q-mx-md">
      <q-card
        flat
        square
        class="q-px-md q-pt-sm q-pb-lg q-mt-md q-gutter-y-lg primary"
      >
        <q-btn
          v-for="provider in providers"
          :key="provider.name"
          align="left"
          flat
          class="full-width"
          size="lg"
          @click="handleProviderBtnClick(provider.name)"
        >
          <template #default>
            <q-icon role="presentation" :name="`fab fa-${provider.icon}`" />
            <span class="q-pl-md">{{
              $t("auth.log_in_external", {
                provider: provider.label
              })
            }}</span>
          </template>
        </q-btn>
      </q-card>
    </section>
  </q-page>
</template>

<script setup lang="ts">
import PasswordInput from "src/components/forms/PasswordInput.vue"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { useQuery } from "@vue/apollo-composable"
import { GET_IDENTITY_PROVIDERS } from "src/graphql/queries"
import { ref, computed } from "vue"
import { useLogin } from "src/use/user"
import { useRouter } from "vue-router"

const error = ref("")
const { loading: loadingProviders, result: resultProviders } = useQuery(
  GET_IDENTITY_PROVIDERS
)
const providers = computed(() => {
  return resultProviders.value?.identityProviders ?? []
})
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

const handleProviderBtnClick = (provider_name) => {
  try {
    window.location.href = providers.value.find(
      (p) => p.name === provider_name
    ).login_url
  } catch (e) {
    error.value = e.message
  }
}
</script>
