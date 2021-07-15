<template>
  <q-page
    class="flex-center flex q-pa-md"
    data-cy="vueRegister"
  >
    <q-card style="width: 400px">
      <q-form
        autofocus
        @submit="handleSubmit"
      >
        <q-card-section class="bg-deep-purple-7">
          <h1 class="text-h5 text-white q-my-xs">
            {{ $t("auth.register") }}
          </h1>
        </q-card-section>

        <q-card-section>
          <p>
            It only takes a minute to create an account and join our community of
            scholars.
          </p>
          <fieldset
            class="q-px-sm q-pb-lg q-gutter-y-lg column"
          >
            <q-input
              v-model.trim="$v.name.$model"
              outlined
              :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
              autocomplete="name"
              data-cy="nameField"
              bottom-slots
            />
            <q-input
              v-model="$v.email.$model"
              outlined
              type="email"
              :label="$t('auth.fields.email')"
              autocomplete="username"
              :error="$v.email.$error"
              debounce="500"
              bottom-slots
              data-cy="email_field"
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.email.$errors"
                  prefix="auth.validation.email"
                />

                <i18n
                  v-if="hasErrorKey('email', 'EMAIL_IN_USE')"
                  path="auth.validation.email.EMAIL_IN_USE_HINT"
                  tag="div"
                  style="line-height: 1.3"
                >
                  <template #loginAction>
                    <router-link to="/login">
                      {{
                        $t("auth.login_help")
                      }}
                    </router-link>
                  </template>
                  <template #passwordAction>
                    <router-link to="/reset-password">
                      {{
                        $t("auth.password_help")
                      }}
                    </router-link>
                  </template>
                  <template #break>
                    <br>
                  </template>
                </i18n>
              </template>
            </q-input>
            <q-input
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
            <new-password-input
              v-model="$v.password.$model"
              outlined
              :label="$t('auth.fields.password')"
              :error="$v.password.$error"
              :complexity="$v.password.notComplex.$response.complexity"
              data-cy="password_field"
            />
          </fieldset>

          <q-banner
            v-if="formErrorMsg"
            dense
            class="form-error text-white bg-red text-center"
            v-text="$t(`auth.failures.${formErrorMsg}`)"
          />
        </q-card-section>
        <q-card-actions class="q-px-lg">
          <q-btn
            unelevated
            size="lg"
            color="deep-purple-7"
            class="full-width text-white"
            :label="$t('auth.register_action')"
            type="submit"
          />
        </q-card-actions>
        <q-card-section class="text-center q-pa-sm">
          <p>
            <router-link
              to="/login"
            >
              {{ $t("auth.register_login") }}
            </router-link>
          </p>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script>
import NewPasswordInput from "../components/forms/NewPasswordInput.vue";
import { useUserValidation } from "src/use/userValidation";
import appAuth from "src/components/mixins/appAuth";
import ErrorFieldRenderer from 'src/components/molecules/ErrorFieldRenderer.vue';
import { useHasErrorKey } from 'src/use/validationHelpers';
import { ref, provide } from '@vue/composition-api';
import {useLogin} from 'src/use/user';

export default {
  name: "PageRegister",
  components: { NewPasswordInput, ErrorFieldRenderer },
  mixins: [appAuth],
  setup(_, {root}) {
    const {loginUser} = useLogin();

    async function handleSubmit() {
      formErrorMsg.value = '';
      try {
        await saveUser();
        await loginUser({email: user.email, password: user.password});
        root.$router.push('/dashboard');
      } catch (e) {
        formErrorMsg.value = e.message;
      }


    }

    const formErrorMsg = ref('');
    const { $v, user, saveUser } = useUserValidation();
    provide('validator', $v);
    const hasErrorKey = useHasErrorKey();

    return {$v, user, handleSubmit, hasErrorKey,  formErrorMsg};
  },
};
</script>
