<template>
  <div class="q-px-lg">
    <h2>{{ $t("header.application_administration") }}</h2>
    <div class="row q-col-gutter-md">
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
              <div class="text-caption text-grey-7">
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
              <div class="text-caption text-grey-7">
                {{ $t("admin.dashboard.publications_description") }}
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

const { push } = useRouter()

function goToUsers() {
  push("/admin/users")
}

function goToPublications() {
  push({ name: "admin:publication:index" })
}
</script>

<style scoped>
.admin-card:hover {
  background-color: #f5f5f5;
  transition: background-color 0.2s;
}
</style>
