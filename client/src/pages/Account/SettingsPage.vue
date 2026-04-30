<template>
  <h1 class="text-h2 q-pl-md" data-cy="page_heading">
    {{ $t("settings.page_title") }}
  </h1>
  <account-profile-form
    ref="form"
    :account-profile="currentUser"
    :graphql-validation="validationErrors"
    @save="updateUser"
  />

  <div class="q-pa-md column q-gutter-y-md">
    <q-card flat bordered>
      <q-card-section class="q-py-sm">
        <h2 class="section-heading">
          {{ $t("settings.preferences.heading") }}
        </h2>
      </q-card-section>
      <q-separator />
      <q-card-section class="column q-gutter-y-md">
        <div>
          <div class="text-caption text-weight-medium text-grey-7 q-mb-xs">
            {{ $t("settings.preferences.theme") }}
          </div>
          <!-- Radio group instead of a select so the three options
               are visible at a glance — there are only ever three. -->
          <div class="row q-gutter-x-md theme-options">
            <q-radio
              v-for="option in themeOptions"
              :key="option.value"
              :model-value="themeValue"
              :val="option.value"
              :label="$t(option.label)"
              :disable="savingPreferences"
              data-cy="theme_option"
              @update:model-value="onThemeChange"
            />
          </div>
        </div>
        <q-toggle
          :model-value="colorBlindPatterns"
          :label="$t('settings.preferences.color_blind_patterns')"
          :disable="savingPreferences"
          data-cy="color_blind_toggle"
          @update:model-value="onColorBlindChange"
        />
      </q-card-section>
    </q-card>

    <q-card flat bordered>
      <q-card-section class="q-py-sm">
        <h2 class="section-heading">
          {{ $t("settings.dismissed.heading") }}
        </h2>
      </q-card-section>
      <q-separator />
      <q-card-section class="row items-center q-gutter-md">
        <div class="col">
          <div class="text-body2">
            {{ $t("settings.dismissed.summary", { n: dismissedKeys.length }) }}
          </div>
          <div class="text-caption text-grey-7">
            {{ $t("settings.dismissed.help") }}
          </div>
        </div>
        <q-btn
          color="primary"
          no-caps
          :disable="dismissedKeys.length === 0"
          :loading="resettingDismissed"
          :label="$t('settings.dismissed.reset')"
          data-cy="reset_dismissed_btn"
          @click="onResetDismissed"
        />
      </q-card-section>
    </q-card>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Co-located mutations: this is the only place users can edit
// their UI preferences, reset dismissals, or toggle beta features.
graphql(`
  mutation UpdateUserPreferences($input: UpdateUserPreferencesInput!) {
    updateUserPreferences(input: $input) {
      id
      preferences {
        theme
        color_blind_patterns
      }
    }
  }
`)

graphql(`
  mutation ResetDismissedUi {
    resetDismissedUi {
      id
      dismissed_ui
    }
  }
`)
</script>

<script setup lang="ts">
import AccountProfileForm from "src/components/forms/AccountProfileForm.vue"
import { UPDATE_USER } from "src/graphql/mutations"
import { useCurrentUser } from "src/use/user"
import { useFeedbackMessages } from "src/use/guiElements"
import { useUserPreferences } from "src/use/userPreferences"
import { useMutation } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"
import {
  ResetDismissedUiDocument,
  UpdateUserPreferencesDocument,
  UserThemePreference
} from "src/graphql/generated/graphql"
import {
  useFormState,
  formStateKey,
  useDirtyGuard,
  useGraphQLValidation
} from "src/use/forms"
import { computed, provide } from "vue"

const { currentUserQuery, currentUser } = useCurrentUser()
const {
  theme: currentTheme,
  colorBlindPatterns,
  dismissedKeys
} = useUserPreferences()

const updateUserMutation = useMutation(UPDATE_USER)

const { mutate, error } = updateUserMutation
const { validationErrors, hasValidationErrors } = useGraphQLValidation(error)

const formState = useFormState(currentUserQuery, updateUserMutation)
provide(formStateKey, formState)

useDirtyGuard(formState.dirty)

const { saved, errorMessage } = formState

const { t } = useI18n()
const $q = useQuasar()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "update_user_notify"
  }
})

async function updateUser(newValues) {
  errorMessage.value = ""
  saved.value = false
  const vars = { id: currentUser.value.id, ...newValues }

  if ((vars?.password?.length ?? 0) === 0) {
    delete vars.password
  }

  try {
    await mutate(vars)
    newStatusMessage("success", t("account.update.success"))
    saved.value = true
  } catch (error) {
    if (hasValidationErrors.value) {
      errorMessage.value = "Unable to save.  Check form for errors."
    } else {
      errorMessage.value = "update_form_internal"
    }
  }
}

// Theme picker: AUTO follows the OS preference; LIGHT and DARK
// override. The radio is bound by-value rather than v-model so a
// failed mutation can leave the previous selection intact.
const themeOptions = [
  { value: UserThemePreference.AUTO, label: "settings.preferences.theme_auto" },
  {
    value: UserThemePreference.LIGHT,
    label: "settings.preferences.theme_light"
  },
  { value: UserThemePreference.DARK, label: "settings.preferences.theme_dark" }
]

// Default radio selection when the user has no stored theme yet.
const themeValue = computed(
  () => currentTheme.value ?? UserThemePreference.AUTO
)

const { mutate: updatePreferencesMutation, loading: savingPreferences } =
  useMutation(UpdateUserPreferencesDocument)

async function onThemeChange(value: UserThemePreference) {
  try {
    await updatePreferencesMutation({ input: { theme: value } })
  } catch {
    newStatusMessage("failure", t("settings.preferences.error"))
  }
}

async function onColorBlindChange(value: boolean) {
  try {
    await updatePreferencesMutation({
      input: { color_blind_patterns: value }
    })
  } catch {
    newStatusMessage("failure", t("settings.preferences.error"))
  }
}

const { mutate: resetDismissedMutation, loading: resettingDismissed } =
  useMutation(ResetDismissedUiDocument)

function onResetDismissed() {
  $q.dialog({
    title: t("settings.dismissed.confirm_title"),
    message: t("settings.dismissed.confirm_body"),
    cancel: true,
    persistent: true,
    ok: {
      label: t("settings.dismissed.reset"),
      color: "primary"
    }
  }).onOk(async () => {
    try {
      await resetDismissedMutation()
      newStatusMessage("success", t("settings.dismissed.success"))
    } catch {
      newStatusMessage("failure", t("settings.dismissed.error"))
    }
  })
}
</script>

<style scoped>
.theme-options {
  flex-wrap: wrap;
}
</style>
