<template>
  <div class="q-px-lg">
    <h2>{{ $t("header.application_administration") }}</h2>
    <div class="row q-col-gutter-md q-pb-lg">
      <div class="col-xs-12 col-sm-6 col-md-4">
        <q-card
          flat
          bordered
          class="cursor-pointer admin-card"
          @click="goToUsers"
        >
          <q-card-section class="row items-center q-gutter-md">
            <q-icon name="groups" size="xl" color="accent" />
            <div>
              <div class="text-subtitle1 text-weight-bold">
                {{ $t("header.user_list") }}
              </div>
              <div class="text-caption">
                {{ $t("admin.dashboard.users_description") }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4">
        <q-card
          flat
          bordered
          class="cursor-pointer admin-card"
          @click="goToPublications"
        >
          <q-card-section class="row items-center q-gutter-md">
            <q-icon name="collections_bookmark" size="xl" color="accent" />
            <div>
              <div class="text-subtitle1 text-weight-bold">
                {{ $t("header.publications") }}
              </div>
              <div class="text-caption">
                {{ $t("admin.dashboard.publications_description") }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-6 col-md-4">
        <q-card
          flat
          bordered
          class="cursor-pointer admin-card"
          @click="goToBetaUsers"
        >
          <q-card-section class="row items-center q-gutter-md">
            <q-icon name="science" size="xl" color="accent" />
            <div>
              <div class="text-subtitle1 text-weight-bold">
                {{ $t("admin.beta_users.title") }}
              </div>
              <div class="text-caption">
                {{ $t("admin.dashboard.beta_users_description") }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from "vue-router"

definePage({
  name: "admin:dashboard",
  // No `crumb` — the page heading already reads "Application
  // Administration" and the parent `routes/admin.vue` contributes
  // the "Administration" crumb, so a self-crumb would duplicate.
  // The `adminArea` gate comes from the parent layout.
  meta: {}
})

const { push } = useRouter()

function goToUsers() {
  push({ name: "admin:users" })
}

function goToPublications() {
  push({ name: "admin:publication:index" })
}

function goToBetaUsers() {
  push({ name: "admin:beta-users" })
}
</script>

<style scoped lang="sass">
.admin-card
  transition: background-color 0.2s;
  .text-caption
    color: $grey-7;
  &:hover
    background-color: #f5f5f5;
    .text-caption
      color: $grey-8;
.body--dark
  .admin-card
    .text-caption
      color: $grey-5;
    &:hover
      background-color: #202020;
</style>
