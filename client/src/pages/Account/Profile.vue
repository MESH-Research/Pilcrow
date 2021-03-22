<template>
  <q-form>
    <q-card-section class="bg-primary text-white">
      <div class="text-subtitle2">
        Personal Details
      </div>
    </q-card-section>
    <q-card-section class="q-gutter-md">
      <q-input
        label="Professional Title"
        outlined
      />
      <q-input
        label="Specialization"
        outlined
        hint="Area of expertise, specialization or research focus."
      />
      <q-input
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
        outlined
        class="col-md-6 col-12"
      >
        <template #prepend>
          <q-icon name="fab fa-twitter" />
        </template>
      </q-input>
      <q-input
        label="Instagram"
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
        class="col-12 q-col-gutter-md"
      >
        <tag-list
          v-model="form.interest_keywords"
          label="Interest Keywords"
        />
        <p>
          Interest keywords will be used to help provide suggestions for submissions which may be of interest to you.
        </p>
      </fieldset>
      <fieldset class="col-12 q-col-gutter-md">
        <tag-list
          v-model="form.disinterest_keywords"
          label="Disinterest Keywords"
        />
        <p>
          Disinterest keywords will be used to help filter suggestions for submissions that are not of interest to you.
        </p>
      </fieldset>
    </q-card-section>
  </q-form>
</template>

<script>
import EditableList from 'src/components/molecules/EditableList.vue';
import TagList from 'src/components/molecules/TagList.vue';

export default {
    name: 'ProfilePage',
    // eslint-disable-next-line vue/no-unused-components
    components: { EditableList, TagList },
    data() {
      return {
        addWebsiteValue: "",
        form: {
          biography: "",
          orchid_id: "",
          humanities_commons: "",
          professional_title: "",
          specialization: "",
          affiliation: "",
          websites: ["test2", "test3", "test4"],
          interest_keywords: ["something", "something else"],
          disinterest_keywords: [],
          social_media: {
            google: "",
            twitter: "",
            facebook: "",
            instagram: "",
            linkedin: "",
            academia_edu_id: "",
          }
        }
      }
    },
    methods: {

    },
}
</script>
