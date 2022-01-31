<template>
  <q-form data-cy="vueAccount" @submit="save()">
    <v-q-wrap t-prefix="account.profile.fields" @vqupdate="updateInput">
      <form-section :first-section="true">
        <template #header>{{ $t("account.profile.section_details") }}</template>

        <v-q-input
          ref="professionalTitle"
          :v="v$.professional_title"
          cy-attr="professional_title"
        />
        <v-q-input ref="specialization" :v="v$.specialization" />
        <v-q-input ref="affiliation" :v="v$.affiliation" />
      </form-section>

      <form-section>
        <template #header>
          {{ $t("account.profile.section_biography") }}
        </template>

        <v-q-input ref="biography" :v="v$.biography" type="textarea" counter>
          <template #counter> {{ form.biography.length }}/4096 </template>
        </v-q-input>
      </form-section>

      <form-section class="social_controls">
        <template #header>
          {{ $t("account.profile.section_social_media") }}
        </template>
        <v-q-input
          ref="facebook"
          :v="v$.social_media.facebook"
          cy-attr="facebook"
          prefix="https://fb.com/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              name="fab fa-facebook"
              :class="{
                'brand-active': v$.social_media.facebook.$model.length,
              }"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="twitter"
          :v="v$.social_media.twitter"
          prefix="https://twitter.com/@"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              :class="{ 'brand-active': v$.social_media.twitter.$model.length }"
              name="fab fa-twitter"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="instagram"
          :v="v$.social_media.instagram"
          prefix="https://instagram.com/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              name="fab fa-instagram-square"
              :class="{
                'brand-active': v$.social_media.instagram.$model.length,
              }"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="linkedin"
          :v="v$.social_media.linkedin"
          prefix="https://linkedin.com/in/"
          class="col-md-6 col-12"
          clearable
        >
          <template #prepend>
            <q-icon
              name="fab fa-linkedin"
              :class="{
                'brand-active': v$.social_media.linkedin.$model.length,
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
          :v="v$.academic_profiles.humanities_commons"
          class="col-md-6 col-12"
        >
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="brand-images/humcommons.png"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="orcid"
          :v="v$.academic_profiles.orcid"
          class="col-md-6 col-12"
        >
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="brand-images/orcid.png"
            />
          </template>
        </v-q-input>
        <v-q-input
          ref="academia_edu_id"
          :v="v$.academic_profiles.academia_edu_id"
          class="col-md-6 col-12"
        >
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="brand-images/academia_edu.png"
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
          v-model="form.websites"
          cy-attr="add_website"
          t="account.profile.fields.website"
          class="q-gutter-md"
          :rules="website_rules"
        />
      </form-section>
      <form-section>
        <template #header>
          {{ $t("account.profile.section_keywords") }}
        </template>
        <fieldset class="col-12 q-col-gutter-sm">
          <tag-list
            ref="interest_keywords"
            v-model="form.interest_keywords"
            t="account.profile.fields.interest_keyword"
            cy-attr="interest_keywords"
            :rules="keyword_rules"
          />
          <p>
            {{ $t("account.profile.fields.interest_keyword.hint") }}
          </p>
        </fieldset>
        <fieldset class="col-12 q-col-gutter-sm">
          <tag-list
            ref="disinterest_keywords"
            v-model="form.disinterest_keywords"
            t="account.profile.fields.disinterest_keyword"
            :rules="keyword_rules"
          />
          <p>
            {{ $t("account.profile.fields.disinterest_keyword.hint") }}
          </p>
        </fieldset>
      </form-section>

      <form-actions @reset-click="resetForm" />
    </v-q-wrap>
  </q-form>
</template>

<script setup>
//Import components
import EditableList from "src/components/molecules/EditableList.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import TagList from "src/components/molecules/TagList.vue"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"

import { computed, reactive, toRef, onMounted, watchEffect, inject } from "vue"
import useVuelidate from "@vuelidate/core"
import {
  rules,
  profile_defaults,
  website_rules,
  keyword_rules,
  useSocialFieldWatchers,
} from "src/use/profileMetadata"
import { useDirtyGuard } from "src/use/forms"
import { isEqual } from "lodash"
import { mapObject } from "src/utils/objUtils"

const props = defineProps({
  profileMetadata: {
    required: true,
    type: Object,
  },
})

const emit = defineEmits(["save"])
const { dirty, errorMessage } = inject("formState")

const profileMetadata = toRef(props, "profileMetadata")

const form = reactive(applyDefaults({}))
const v$ = useVuelidate(rules, form)
useSocialFieldWatchers(form)

const original = computed(() => {
  return applyDefaults(profileMetadata.value)
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

function updateInput(validator, newValue) {
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

function applyDefaults(data) {
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
