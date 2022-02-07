<template>
  <div>
    <account-profile-form
      ref="form"
      :account-profile="currentUser"
      @save="updateUser"
    />
  </div>
</template>

<script setup>
import { UPDATE_USER } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import { useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useFormState, useDirtyGuard } from "src/use/forms"
import { provide } from "vue"
import AccountProfileForm from "src/components/forms/AccountProfileForm.vue"
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

const { currentUserQuery, currentUser } = useCurrentUser()

const updateUserMutation = useMutation(UPDATE_USER)
const { mutate } = updateUserMutation

const formState = useFormState(
  currentUserQuery.loading,
  updateUserMutation.loading
)
provide("formState", formState)

useDirtyGuard(formState.dirty)

const { saved, errorMessage } = formState

const { notify } = useQuasar()
const { t } = useI18n()

async function updateUser(newValues) {
  errorMessage.value = ""
  saved.value = false
  const vars = { ...newValues }

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
    saved.value = true
  } catch (error) {
    if (importValidationErrors(error, this)) {
      errorMessage.value = "update_form_validation"
    } else {
      errorMessage.value = "update_form_internal"
    }
  }
}
</script>
