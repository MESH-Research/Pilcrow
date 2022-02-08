<template>
  <div>
    <account-profile-form
      ref="form"
      :account-profile="currentUser"
      :graphql-validation="validationErrors"
      @save="updateUser"
    />
  </div>
</template>

<script setup>
import AccountProfileForm from "src/components/forms/AccountProfileForm.vue"
import { UPDATE_USER } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import { useQuasar } from "quasar"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import {
  useFormState,
  useDirtyGuard,
  useGraphQLValidation,
} from "src/use/forms"
import { provide } from "vue"

const { currentUserQuery, currentUser } = useCurrentUser()

const updateUserMutation = useMutation(UPDATE_USER)

const { mutate, error } = updateUserMutation
const { validationErrors, hasValidationErrors } = useGraphQLValidation(error)

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
  const vars = { id: currentUser.value.id, ...newValues }

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
    if (hasValidationErrors.value) {
      errorMessage.value = "Unable to save.  Check form for errors."
    } else {
      errorMessage.value = "update_form_internal"
    }
  }
}
</script>
