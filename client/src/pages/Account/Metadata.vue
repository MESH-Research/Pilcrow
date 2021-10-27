<template>
  <q-form data-cy="vueAccount" @submit="save()">
    <form-section>
      <template #header>{{ $t("account.profile.section_details") }}</template>
      <q-input
        v-model="form.professional_title"
        :label="$t('account.profile.fields.professional_title')"
        outlined
      />
      <q-input
        v-model="form.specialization"
        :label="$t('account.profile.fields.specialization')"
        outlined
        :hint="$t('account.profile.fields.specialization_hint')"
      />
      <q-input
        v-model="form.affiliation"
        :label="$t('account.profile.fields.affiliation')"
        outlined
        :hint="$t('account.profile.fields.affiliation_hint')"
      />
    </form-section>
    <form-section>
      <template #header>
        {{ $t("account.profile.section_biography") }}
      </template>
      <q-input
        v-model="form.biography"
        :label="$t('account.profile.fields.biography')"
        outlined
        type="textarea"
        counter
      >
        <template #counter> {{ form.biography.length }}/4096 </template>
      </q-input>
    </form-section>
    <form-section>
      <template #header>
        {{ $t("account.profile.section_social_media") }}
      </template>
      <q-input
        v-model="form.social_media.facebook"
        label="Facebook"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-facebook" />
        </template>
      </q-input>
      <q-input label="Twitter" prefix="@" outlined class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-twitter" />
        </template>
      </q-input>
      <q-input label="Instagram" prefix="@" outlined class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-instagram" />
        </template>
      </q-input>
      <q-input label="LinkedIn" outlined class="col-md-6 col-12">
        <template #prepend>
          <q-icon name="fab fa-linkedin" />
        </template>
      </q-input>
    </form-section>
    <form-section>
      <template #header>
        {{ $t("account.profile.section_academic_profiles") }}
      </template>
      <q-input label="Academia.edu" outlined class="col-md-6 col-12">
        <template #prepend>
          <img
            style="height: 1em; display: inline-block"
            src="brand-images/academia_edu.png"
          />
        </template>
      </q-input>
      <q-input label="Humanities Commons" outlined class="col-md-6 col-12">
        <template #prepend>
          <img
            style="height: 1em; display: inline-block"
            src="brand-images/humcommons.png"
          />
        </template>
      </q-input>
      <q-input label="Orcid ID" outlined class="col-md-6 col-12">
        <template #prepend>
          <img
            style="height: 1em; display: inline-block"
            src="brand-images/orcid.png"
          />
        </template>
      </q-input>
    </form-section>
    <form-section>
      <template #header>
        {{ $t("account.profile.section_websites") }}
      </template>

      <editable-list
        v-model="form.websites"
        :item-name="$t('account.profile.fields.website')"
        class="q-gutter-md"
      />
    </form-section>
    <form-section>
      <template #header>
        {{ $t("account.profile.section_keywords") }}
      </template>
      <fieldset class="col-12 q-col-gutter-sm">
        <tag-list
          v-model="form.interest_keywords"
          :item-name="$t('account.profile.fields.interest_keyword')"
        />
        <p>
          {{ $t("account.profile.fields.interest_keyword_hint") }}
        </p>
      </fieldset>
      <fieldset class="col-12 q-col-gutter-sm">
        <tag-list
          v-model="form.disinterest_keywords"
          :item-name="$t('account.profile.fields.disinterest_keyword')"
        />
        <p>
          {{ $t("account.profile.fields.disinterest_keyword_hint") }}
        </p>
      </fieldset>
    </form-section>
    <form-actions>
      <q-btn
        :disabled="!dirty"
        class="text-white"
        :class="saveBtn.class"
        data-cy="button_save"
        type="submit"
      >
        <q-icon v-if="saved === true && !dirty" name="check" />
        <q-spinner v-else-if="saving === true" />
        {{ $t(saveBtn.text) }}
      </q-btn>
      <q-btn
        :disabled="!dirty"
        class="bg-grey-4 ml-sm"
        data-cy="button_discard"
        @click="resetForm"
      >
        {{ $t("buttons.discard_changes") }}
      </q-btn>
    </form-actions>
  </q-form>
</template>

<script>
import EditableList from "src/components/molecules/EditableList.vue"
import TagList from "src/components/molecules/TagList.vue"
import FormSection from "src/components/molecules/FormSection.vue"
import {
  defineComponent,
  computed,
  reactive,
  ref,
  watch,
} from "@vue/composition-api"
import useVuelidate from "@vuelidate/core"
import rules from "src/use/profileMetadataValidation"
import { useMutation, useQuery, useResult } from "@vue/apollo-composable"
import { CURRENT_USER_METADATA } from "src/graphql/queries"
import { UPDATE_PROFILE_METADATA } from "src/graphql/mutations"
import { isEqual } from "lodash"
import { mapObject } from "src/utils/objUtils"
import dirtyGuard from "components/mixins/dirtyGuard"
import FormActions from "src/components/molecules/FormActions.vue"
const applyDefaults = (data) => {
  const defaults = {
    biography: "",

    professional_title: "",
    specialization: "",
    affiliation: "",
    websites: [],
    interest_keywords: [],
    disinterest_keywords: [],
    social_media: {
      google: "",
      twitter: "",
      facebook: "",
      instagram: "",
      linkedin: "",
    },
    academic_profiles: {
      orcid_id: "",
      academia_edu_id: "",
      humanities_commons: "",
    },
  }
  console.log(data)
  return JSON.parse(JSON.stringify(mapObject(defaults, data)))
}

export default defineComponent({
  name: "ProfilePage",
  components: { EditableList, TagList, FormSection, FormActions },
  mixins: [dirtyGuard],
  setup() {
    const saved = ref(false)

    const form = reactive(applyDefaults({}))
    const v$ = useVuelidate(rules, form)

    const original = computed(() => {
      return applyDefaults(profile_metadata.value)
    })

    const saveBtn = reactive({
      class: computed(() => {
        if (saving.value) {
          return {}
        } else if (saved.value && dirty.value) {
          return { "bg-positive": true }
        } else {
          return { "bg-primary": true }
        }
      }),
      text: computed(() => {
        if (saving.value) {
          return "buttons.saving"
        } else if (saved.value && !dirty.valuey) {
          return "buttons.saved"
        } else {
          return "buttons.save"
        }
      }),
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
      return !isEqual(original.value, form.value)
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
      saved,
      saving,
      form,
      dirty,
      original,
      resetForm,
      save,
      v$,
      saveBtn,
    }
  },
})
</script>
