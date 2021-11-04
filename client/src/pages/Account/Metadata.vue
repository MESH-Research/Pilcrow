<template>
  <q-form
    data-cy="vueAccount"
    t="account.profile.fields"
    @submit="save()"
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

    <form-section>
      <template #header>
        {{ $t("account.profile.section_social_media") }}
      </template>
      <v-q-input :v="v$.social_media.facebook" class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-facebook" />
        </template>
      </v-q-input>
      <v-q-input
        :v="v$.social_media.twitter"
        prefix="@"
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-twitter" />
        </template>
      </v-q-input>
      <v-q-input :v="v$.social_media.instagram" class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-instagram" />
        </template>
      </v-q-input>
      <v-q-input :v="v$.social_media.linkedin" class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-linkedin" />
        </template>
      </v-q-input>
    </form-section>

    <form-section>
      <template #header>
        {{ $t("account.profile.section_academic_profiles") }}
      </template>
      <v-q-input :v="v$.academic_profiles.academia_edu" class="col-md-6 col-12">
        <template #prepend>
          <img
            style="height: 1em; display: inline-block"
            src="brand-images/academia_edu.png"
          />
        </template>
      </v-q-input>
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

    <form-actions :form-state="formState" @resetClick="resetForm" />
  </q-form>
</template>

<script>
//Import components
import EditableList from "src/components/molecules/EditableList.vue"
import VQInput from "src/components/atoms/VQInput.vue"
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
} from "src/composables/profileMetadata"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { CURRENT_USER_METADATA } from "src/graphql/queries"
import { UPDATE_PROFILE_METADATA } from "src/graphql/mutations"
import { isEqual } from "lodash"
import { mapObject } from "src/utils/objUtils"

export default defineComponent({
  name: "ProfilePage",
  components: { EditableList, TagList, FormSection, FormActions, VQInput },
  setup() {
    const saved = ref(false)

    const form = reactive(applyDefaults({}))
    const v$ = useVuelidate(rules, form)

    const original = computed(() => {
      return applyDefaults(profile_metadata.value)
    })

    const { result } = useQuery(CURRENT_USER_METADATA)
    const profile_metadata = useResult(
      result,
      applyDefaults({}),
      (data) => data.currentUser.profile_metadata
    )
    const currentUserId = useResult(result, {}, (data) => data.currentUser.id)

    watch(currentUserId, () => Object.assign(form, original.value))

    const dirty = computed(() => {
      return !isEqual(original.value, form)
    })

    const formState = computed(() => {
      if (dirty.value) {
        return "dirty"
      }
      if (saved.value) {
        return "saved"
      }
      if (saving.value) {
        return "saving"
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
      Object.assign(form, original.value)
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
    }
  },
})
function applyDefaults(data) {
  return JSON.parse(JSON.stringify(mapObject(profile_defaults, data)))
}
</script>
