<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-else-if="status == 'register'">
        <q-form style="width: 400px" @submit="handleSubmit">
          <h1>{{ $t("auth.oauth.register.title") }}</h1>
          <fieldset class="q-pb-lg q-px-none q-gutter-y-lg column">
            <q-input
              ref="nameInput"
              v-model.trim="$v.name.$model"
              outlined
              :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
              autocomplete="name"
              data-cy="name_field"
              bottom-slots
            />
            <q-input
              ref="usernameInput"
              v-model.trim="$v.username.$model"
              outlined
              :label="$t('auth.fields.username')"
              :error="$v.username.$error"
              autocomplete="nickname"
              debounce="500"
              data-cy="username_field"
              bottom-slots
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.username.$errors"
                  prefix="auth.validation.username"
                />
              </template>
            </q-input>
            <q-input
              ref="emailInput"
              v-model.trim="email"
              outlined
              disable
              type="email"
              :label="$t('auth.fields.email')"
              bottom-slots
              data-cy="email_field"
            />
          </fieldset>

          <q-card-actions class="q-py-lg q-px-none">
            <q-btn
              unelevated
              size="lg"
              color="accent"
              class="full-width text-white"
              :label="
                $t('submissions.accept_invite.update_details.update_action')
              "
              type="submit"
            />
          </q-card-actions>
        </q-form>
      </div>
      <div v-else-if="status == 'auth'">
        <p>Time to auth</p>
      </div>
      <div v-else>
        <p>Whoops</p>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import { useMutation } from "@vue/apollo-composable"
import { useUserValidation } from "src/use/userValidation"
import { LOGIN_ORCID_CALLBACK,  } from "src/graphql/mutations"
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_ORCID_CALLBACK, {
  variables: { code: code },
})

const id = ref(null)
const email = ref("")
// const { mutate: finish_oauth } = useMutation(ACCEPT_SUBMISSION_INVITE)
const status = ref("loading")
const { $v, user, saveUser } = useUserValidation({
  // mutation: finish_oauth,
  rules: (rules) => {
    delete rules.email
  },
  variables: (form) => {
    return { id, ...form }
  },
})

onMounted(async () => {
  console.log(saveUser)
  try {
    const response = await handleCallback()
    const data = response.data.loginOrcidCallback
    status.value = data.status
    Object.assign(user, data.user)
    console.log(data)
  } catch (error) {
    console.error(error)
  }
})
</script>
