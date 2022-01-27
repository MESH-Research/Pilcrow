<template>
  <div>
    <q-form data-cy="vueAccount" @submit="updateUser()">
      <form-section :first-section="true">
        <template #header>Account Information</template>
        <q-input
          ref="nameInput"
          v-model="form.name"
          outlined
          data-cy="update_user_name"
          label="Display Name"
        />
        <q-input
          ref="emailInput"
          v-model="form.email"
          outlined
          data-cy="update_user_email"
          label="Email"
        />
        <q-input
          ref="usernameInput"
          v-model="form.username"
          outlined
          data-cy="update_user_username"
          label="Username"
        />
      </form-section>
      <form-section>
        <template #header>Update Password</template>
        <q-input
          ref="passwordInput"
          v-model="form.password"
          outlined
          data-cy="update_user_password"
          :type="isPwd ? 'password' : 'text'"
          label="Password"
          hint="Updating this will overwrite the existing password"
        >
          <template #append>
            <q-icon
              :name="isPwd ? 'visibility_off' : 'visibility'"
              class="cursor-pointer"
              @click="isPwd = !isPwd"
            />
          </template>
        </q-input>
        <q-banner
          v-if="formErrorMsg"
          dense
          class="form-error text-white bg-red text-center"
          v-text="$t(`account.update.${formErrorMsg}`)"
        />
      </form-section>
      <form-actions>{{ dirty }} </form-actions>
    </q-form>
  </div>
</template>

<script setup>
import { isEqual, pick } from "lodash"
import { UPDATE_USER } from "src/graphql/mutations"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import { useCurrentUser } from "src/use/user"
import { useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import { reactive, ref, computed, onMounted } from "vue"
import { useI18n } from "vue-i18n"

const importValidationErrors = function (error, vm) {
  const gqlErrors = error?.graphQLErrors ?? []
  var hasVErrors = false
  gqlErrors.forEach((item) => {
    const vErrors = item?.extensions?.validation ?? false
    if (vErrors !== false) {
      for (const [fieldName, fieldErrors] of Object.entries(vErrors)) {
        vm.serverValidationErrors[fieldName] = fieldErrors
      }
      hasVErrors = true
    }
  })
  return hasVErrors
}

const form = reactive({
  id: null,
  name: "",
  email: "",
  username: "",
  password: "",
})
const isPwd = ref(true)
const formErrorMsg = ref("")

const { currentUser } = useCurrentUser()

const dirty = computed(() => {
  return !isEqual(form, currentUser)
})

function pickFields(obj) {
  return pick(obj, Object.keys(form))
}

function onRevert() {
  Object.assign(form, pickFields(currentUser.value))
}

onMounted(() => {
  onRevert()
})

const { mutate } = useMutation(UPDATE_USER)
const { notify } = useQuasar()
const { t } = useI18n()

async function updateUser() {
  formErrorMsg.value = ""
  const vars = { ...form }

  if ((vars?.password?.length ?? 0) === 0) {
    delete vars.password
  }

  try {
    await mutate(vars)
    //TODO: Refactor to use makeNofity composable
    notify({
      color: "positive",
      message: t("account.update.success"),
      icon: "check_circle",
      attrs: {
        "data-cy": "update_user_notify",
      },
      html: true,
    })
  } catch (error) {
    if (importValidationErrors(error, this)) {
      formErrorMsg.value = "update_form_validation"
    } else {
      formErrorMsg.value = "update_form_internal"
    }
  }
}
</script>
