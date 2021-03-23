<template>
  <q-form @submit="save">
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        Personal Details
      </div>
    </q-card-section>
    <q-card-section class="q-gutter-md">
      <q-input
        v-model="form.professional_title"
        label="Professional Title"
        outlined
      />
      <q-input
        v-model="form.specialization"
        label="Specialization"
        outlined
        hint="Area of expertise, specialization or research focus."
      />
      <q-input
        v-model="form.affiliation"
        label="Affiliation"
        outlined
        hint="Institutional, group, or organization affiliation."
      />
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        Biography
      </div>
    </q-card-section>
    <q-card-section class="q-gutter-md">
      <q-input
        v-model="form.biography"
        label="Biography"
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
        Social Media Profiles
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
        Academic Profiles
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
        Websites
      </div>
    </q-card-section>
    <q-card-section>
      <editable-list
        v-model="form.websites"
        label="Add Website"
        class="q-gutter-md"
      />
    </q-card-section>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        Keywords
      </div>
    </q-card-section>
    <q-card-section class="q-col-gutter-md row">
      <fieldset
        class="col-12 q-col-gutter-sm"
      >
        <tag-list
          v-model="form.interest_keywords"
          label="Interest Keywords"
        />
        <p>
          Interest keywords will be used to help provide suggestions for submissions which may be of interest to you.
        </p>
      </fieldset>
      <fieldset class="col-12 q-col-gutter-sm">
        <tag-list
          v-model="form.disinterest_keywords"
          label="Disinterest Keywords"
        />
        <p>
          Disinterest keywords will be used to help filter suggestions for submissions that are not of interest to you.
        </p>
      </fieldset>
    </q-card-section>
    <q-card-section class="bg-grey-2 row justify-end">
      <div class="q-gutter-md">
        <q-btn
          :disabled="!dirty"
          class="text-white"
          :class="saveBtnClass"
          data-cy="update_user_button_save"
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
          data-cy="update_user_button_discard"
          @click="resetForm"
        >
          Discard Changes
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
          return 'Saving';
        } else if (this.saved && !this.dirty) {
          return "Saved";
        } else {
          return "Save";
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
