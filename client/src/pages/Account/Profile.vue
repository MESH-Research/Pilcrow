<template>
  <q-form @submit="save">
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_personal') }}
      </div>
    </q-card-section>
    <q-card-section class="q-gutter-md">
      <q-input
        v-model="form.professional_title"
        :label="$t('account.profile.fields.professional_title')"
        outlined
      />
      <q-input
        v-model="form.specialization"
        :label="$t('account.profile.fields.specialization')"
        outlined
        :hint="$t('account.profile.fields.specializztion_hint')"
      />
      <q-input
        v-model="form.affiliation"
        :label="$t('account.profile.fields.affiliation')"
        outlined
        :hint="$t('account.profile.fields.affiliation_hint')"
      />
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_biography') }}
      </div>
    </q-card-section>
    <q-card-section class="q-gutter-md">
      <q-input
        v-model="form.biography"
        :label="$t('account.profile.fields.biography')"
        outlined
        type="textarea"
        counter
      >
        <template #counter>
          {{ form.biography.length }}/4096
        </template>
      </q-input>
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_social_media') }}
      </div>
    </q-card-section>
    <q-card-section class="q-col-gutter-md row">
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
      <q-input
        label="Twitter"
        prefix="@"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-twitter" />
        </template>
      </q-input>
      <q-input
        label="Instagram"
        prefix="@"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-instagram" />
        </template>
      </q-input>
      <q-input
        label="LinkedIn"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-linkedin" />
        </template>
      </q-input>
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_academic_profiles') }}
      </div>
    </q-card-section>
    <q-card-section class="q-col-gutter-md row">
      <q-input
        label="Academia.edu"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <img
            style="height: 1em; display: inline-block;"
            src="brand-images/academia_edu.png"
          >
        </template>
      </q-input>
      <q-input
        label="Humanities Commons"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <img
            style="height: 1em; display: inline-block;"
            src="brand-images/humcommons.png"
          >
        </template>
      </q-input>
      <q-input
        label="Orchid ID"
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <img
            style="height: 1em; display: inline-block;"
            src="brand-images/orchid.png"
          >
        </template>
      </q-input>
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_websites') }}
      </div>
    </q-card-section>
    <q-card-section>
      <editable-list
        v-model="form.websites"
        :item-name="$t('account.profile.fields.website')"
        class="q-gutter-md"
      />
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        {{ $t('account.profile.section_keywords') }}
      </div>
    </q-card-section>
    <q-card-section class="q-col-gutter-md row">
      <fieldset
        class="col-12 q-col-gutter-sm"
      >
        <tag-list
          v-model="form.interest_keywords"
          :item-name="$t('account.profile.fields.interest_keyword')"
        />
        <p>
          {{ $t('account.profile.fields.interest_keyword_hint') }}
        </p>
      </fieldset>
      <fieldset class="col-12 q-col-gutter-sm">
        <tag-list
          v-model="form.disinterest_keywords"
          :item-name="$t('account.profile.fields.disinterest_keyword')"
        />
        <p>
          {{ $t('account.profile.fields.disinterest_keyword_hint') }}
        </p>
      </fieldset>
    </q-card-section>
    <q-card-section class="bg-grey-2 row justify-end">
      <div class="q-gutter-md">
        <q-btn
          :disabled="!dirty"
          class="text-white"
          :class="saveBtnClass"
          data-cy="button_save"
          type="submit"
        >
          <q-icon
            v-if="saved === true && !dirty"
            name="check"
          />
          <q-spinner v-else-if="saving === true" />
          {{ saveBtnText }}
        </q-btn>
        <q-btn
          :disabled="!dirty"
          class="bg-grey-4 ml-sm"
          data-cy="button_discard"
          @click="resetForm"
        >
          {{ $t('buttons.discard_changes') }}
        </q-btn>
      </div>
    </q-card-section>
  </q-form>
</template>

<script>
import EditableList from 'src/components/molecules/EditableList.vue';
import TagList from 'src/components/molecules/TagList.vue';

import { CURRENT_USER } from 'src/graphql/queries';
import { UPDATE_PROFILE_METADATA } from 'src/graphql/mutations';

import { isEqual } from "lodash";
import { mapObject } from 'src/utils/objUtils';
import dirtyGuard from "components/mixins/dirtyGuard";

const applyDefaults = (data) => {
  const defaults = {
    biography: "",
    orchid_id: "",
    humanities_commons: "",
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
      academia_edu_id: "",
    }
  };

  return JSON.parse(JSON.stringify(mapObject(defaults, data)));
};

export default {
    name: 'ProfilePage',
    components: { EditableList, TagList },
    mixins: [dirtyGuard],
    data() {
      return {
        saved: false,
        saving: false,
        form: applyDefaults({})
      }
    },
    apollo: {
      currentUser: {
        query: CURRENT_USER
      }
    },
    computed: {
      dirty() {
        return !isEqual(this.original, this.form);
      },
      original() {
       return applyDefaults(this.currentUser.profile_metadata);
      },
      saveBtnClass() {
        if (this.saving) {
          return { };
        } else if (this.saved && !this.dirty) {
          return {'bg-positive': true};
        } else {
          return {'bg-primary': true};
        }
      },
      saveBtnText() {
        if (this.saving) {
          return this.$t('buttons.saving');
        } else if (this.saved && !this.dirty) {
          return this.$t('buttons.saved');
        } else {
          return this.$t('buttons.save');
        }
      }
    },
    watch: {
      currentUser() {
        this.resetForm();
      }
    },
    methods: {
      resetForm() {
        this.form = applyDefaults(this.currentUser.profile_metadata);
        this.saved = false;
      },
      save() {
        this.saved = false;
        this.saving = true;
        this.$apollo.mutate(
          {
            mutation: UPDATE_PROFILE_METADATA,
            variables: {id: this.currentUser.id, ...this.form}
          }
        ).then(() => {
          this.saved = true;
        }).catch(() => {
          this.saved = false;
        }).finally(() => {
          this.saving = false;
        });
      }
    },
}
</script>
