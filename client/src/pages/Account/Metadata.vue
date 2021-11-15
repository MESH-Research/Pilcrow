<template>
  <q-form data-cy="vueAccount" @submit="save()">
    <v-q-wrap
      t-prefix="account.profile.fields"
      :form-state="formState"
      @vqupdate="updateInput"
    >
      <form-section :first-section="true">
        <template #header>{{ $t("account.profile.section_details") }}</template>

        <v-q-input :v="v$.professional_title" />
        <v-q-input :v="v$.specialization" />
        <v-q-input :v="v$.affiliation" />
      </form-section>

      <form-section>
        <template #header>
          {{ $t("account.profile.section_biography") }}
        </template>

        <v-q-input :v="v$.biography" type="textarea" counter>
          <template #counter> {{ form.biography.length }}/4096 </template>
        </v-q-input>
      </form-section>

      <form-section class="social_controls">
        <template #header>
          {{ $t("account.profile.section_social_media") }}
        </template>
        <v-q-input
          :v="v$.social_media.facebook"
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
        <v-q-input :v="v$.academic_profiles.orcid" class="col-md-6 col-12">
          <template #prepend>
            <img
              style="height: 1em; display: inline-block"
              src="brand-images/orcid.png"
            />
          </template>
        </v-q-input>
        <v-q-input
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
          v-model="form.websites"
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
            v-model="form.interest_keywords"
            t="account.profile.fields.interest_keyword"
            :rules="keyword_rules"
          />
          <p>
            {{ $t("account.profile.fields.interest_keyword.hint") }}
          </p>
        </fieldset>
        <fieldset class="col-12 q-col-gutter-sm">
          <tag-list
            v-model="form.disinterest_keywords"
            t="account.profile.fields.disinterest_keyword"
            :rules="keyword_rules"
          />
          <p>
            {{ $t("account.profile.fields.disinterest_keyword.hint") }}
          </p>
        </fieldset>
      </form-section>

      <form-actions :form-state="formState" class="" @resetClick="resetForm" />
    </v-q-wrap>
  </q-form>
</template>

<script>
//Import components
import EditableList from "src/components/molecules/EditableList.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import TagList from "src/components/molecules/TagList.vue"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"

import {
  defineComponent,
  computed,
  reactive,
  ref,
  watch,
} from "@vue/composition-api"
import useVuelidate from "@vuelidate/core"
import {
  rules,
  profile_defaults,
  website_rules,
  keyword_rules,
  useSocialFieldWatchers,
} from "src/composables/profileMetadata"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { useDirtyGuard } from "src/composables/forms"
import { CURRENT_USER_METADATA } from "src/graphql/queries"
import { UPDATE_PROFILE_METADATA } from "src/graphql/mutations"
import { isEqual } from "lodash"
import { mapObject } from "src/utils/objUtils"

export default defineComponent({
  name: "ProfilePage",
  components: {
    EditableList,
    TagList,
    FormSection,
    FormActions,
    VQInput,
    VQWrap,
  },
  setup(_, context) {
    const saved = ref(false)

    const form = reactive(applyDefaults({}))
    const v$ = useVuelidate(rules, form)
    useSocialFieldWatchers(form)

    const original = computed(() => {
      return applyDefaults(profile_metadata.value)
    })

    const { result, loading } = useQuery(CURRENT_USER_METADATA)
    const profile_metadata = useResult(
      result,
      applyDefaults({}),
      (data) => data.currentUser.profile_metadata
    )

    const currentUserId = useResult(result, {}, (data) => data.currentUser.id)
    Object.assign(form, applyDefaults(original.value))
    watch(currentUserId, () =>
      Object.assign(form, applyDefaults(original.value))
    )

    const dirty = computed(() => {
      return !isEqual(original.value, form)
    })

    useDirtyGuard(dirty, context)

    const formState = computed(() => {
      if (loading.value) {
        return "loading"
      }
      if (saving.value) {
        return "saving"
      }
      if (dirty.value) {
        return "dirty"
      }
      if (saved.value) {
        return "saved"
      }
      return "idle"
    })

    const { mutate: saveProfile, loading: saving } = useMutation(
      UPDATE_PROFILE_METADATA
      //   () => ({
      //     update: (cache) => {
      //       cache.writeQuery({
      //         query: CURRENT_USER,
      //         data: { currentUser: {} },
      //       })
      //     },
      //   })
    )

    function resetForm() {
      Object.assign(form, applyDefaults(original.value))
      saved.value = false
    }

    function updateInput(validator, newValue) {
      validator.$model = newValue
    }

    function save() {
      saved.value = false
      saveProfile({ id: currentUserId.value, ...form })
        .then(() => {
          saved.value = true
        })
        .catch(() => {
          saved.value = false
        })
    }

    return {
      form,
      resetForm,
      save,
      v$,
      formState,
      updateInput,
      website_rules,
      keyword_rules,
      original,
      profile_metadata,
    }
  },
})
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
