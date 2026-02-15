<template>
  <q-form data-cy="vueAccount" @submit="save()">
    <v-q-wrap t-prefix="account.profile.fields" @vqupdate="updateInput">
      <form-section :first-section="true">
        <v-q-input
          ref="usernameInput"
          :v="v$.username"
          data-cy="update_user_username"
        />
        <v-q-input ref="nameInput" :v="v$.name" data-cy="update_user_name" />

        <v-q-input
          ref="positionTitle"
          :v="vProfile.position_title"
          data-cy="position_title"
        />
        <v-q-input ref="specialization" :v="vProfile.specialization" />
        <v-q-input ref="affiliation" :v="vProfile.affiliation" />
      </form-section>

      <form-section>
        <template #header>
          {{ $t("account.profile.section_biography") }}
        </template>

        <v-q-input
          ref="biography"
          :v="vProfile.biography"
          type="textarea"
          counter
        >
          <template #counter>
            {{ form.profile_metadata.biography.length }}/4096
          </template>
        </v-q-input>
      </form-section>

      <form-section class="social_controls">
        <template #header>
          {{ $t("account.profile.section_social_media") }}
        </template>
        <v-q-input
          ref="facebook"
          :v="vProfile.social_media.facebook"
          data-cy="facebook"
          prefix="https://fb.com/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              role="presentation"
              name="fab fa-facebook"
              :class="{
                'brand-active': vProfile.social_media.facebook.$model.length
              }"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="twitter"
          :v="vProfile.social_media.twitter"
          prefix="https://twitter.com/@"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              role="presentation"
              :class="{
                'brand-active': vProfile.social_media.twitter.$model.length
              }"
              name="fab fa-twitter"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="instagram"
          :v="vProfile.social_media.instagram"
          prefix="https://instagram.com/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              name="fab fa-instagram-square"
              role="presentation"
              :class="{
                'brand-active': vProfile.social_media.instagram.$model.length
              }"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="linkedin"
          :v="vProfile.social_media.linkedin"
          prefix="https://linkedin.com/in/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              name="fab fa-linkedin"
              role="presentation"
              :class="{
                'brand-active': vProfile.social_media.linkedin.$model.length
              }"
            />
          </template>
        </v-q-input>
      </form-section>

      <form-section>
        <template #header>
          {{ $t("account.profile.section_academic_profiles") }}
        </template>
        <v-q-input
          ref="humanities_commons"
          :v="vProfile.academic_profiles.humanities_commons"
          class="col-md-6 col-12"
        >
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="/brand-images/humcommons.png"
              role="presentation"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="orcid_id"
          :v="vProfile.academic_profiles.orcid_id"
          class="col-md-6 col-12"
        >
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="/brand-images/orcid.png"
              role="presentation"
            />
          </template>
        </v-q-input>
      </form-section>
      <form-section>
        <template #header>
          {{ $t("account.profile.section_websites") }}
        </template>

        <editable-list
          ref="websites"
          v-model="form.profile_metadata.websites"
          t="account.profile.fields.profile_metadata.websites"
          data-cy="websites_list_control"
          class="q-gutter-md"
          :rules="website_rules"
        />
      </form-section>

      <form-actions @reset-click="resetForm" />
    </v-q-wrap>
  </q-form>
</template>

<script setup lang="ts">
import EditableList from "src/components/molecules/EditableList.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"

import { computed, reactive, toRef, onMounted, watchEffect, inject } from "vue"
import useVuelidate from "@vuelidate/core"
import {
  rules,
  profile_defaults,
  website_rules,
  useSocialFieldWatchers
} from "src/use/profileMetadata"
import type { ProfileFormData } from "src/use/profileMetadata"
import { useDirtyGuard, formStateKey } from "src/use/forms"
import { isEqual } from "lodash"
import { mapObject } from "src/utils/objUtils"
import type { VuelidateValidator } from "src/types/vuelidate"

interface Props {
  profileMetadata: Partial<ProfileFormData> | null | undefined
}

const props = defineProps<Props>()

interface Emits {
  save: [form: ProfileFormData]
}

const emit = defineEmits<Emits>()

const { dirty, errorMessage } = inject(formStateKey)!

const profileMetadata = toRef(props, "profileMetadata")

const form = reactive(applyDefaults({}))
const v$ = useVuelidate(rules, form)
// Vuelidate's deep conditional types cause vue-tsc to flag nested validators as
// possibly undefined. The rules guarantee profile_metadata always exists at runtime.
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const vProfile = computed(() => (v$.value as any).profile_metadata)
useSocialFieldWatchers(form)

const original = computed(() => {
  const originalData = profileMetadata.value ?? {}
  return applyDefaults(originalData)
})

watchEffect(() => {
  dirty.value = !isEqual(original.value, form)
})
useDirtyGuard(dirty)

function resetForm() {
  Object.assign(form, applyDefaults(original.value))
}

watchEffect(() => {
  Object.assign(form, applyDefaults(original.value))
})

onMounted(() => {
  resetForm()
})

function updateInput(validator: VuelidateValidator, newValue: unknown) {
  validator.$model = newValue
}

function save() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    errorMessage.value = "Oops, check form above for errors"
  } else {
    emit("save", form)
  }
}

function applyDefaults(data: Partial<ProfileFormData>): ProfileFormData {
  return JSON.parse(JSON.stringify(mapObject(profile_defaults, data)))
}
</script>

<style lang="sass">
.social_controls
  .q-field__prefix
    padding-right: 0px
  .q-field__prepend
    .q-icon
      color: #cdcdcd
      &.brand-active
        &.fa-facebook
          color: #3b5998
        &.fa-twitter
          color: #00aced
        &.fa-instagram-square
          color: #dc3d86
        &.fa-linkedin
          color: #007bb6
</style>
