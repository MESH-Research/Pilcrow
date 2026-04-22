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
          <q-card-section class="row items-center no-wrap q-gutter-md">
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
          <q-card-section class="row items-center no-wrap q-gutter-md">
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
      <div class="col-xs-12 col-sm-6 col-md-4">
        <q-card
          flat
          bordered
          class="cursor-pointer admin-card"
          data-cy="admin_card_avatar_reports"
          @click="goToAvatarReports"
        >
          <q-card-section class="row items-center no-wrap q-gutter-md">
            <div class="relative-position">
              <q-icon name="flag" size="xl" color="accent" />
              <q-badge
                v-if="pendingReportsCount > 0"
                floating
                color="red"
                :label="String(pendingReportsCount)"
                data-cy="admin_card_avatar_reports_badge"
              />
            </div>
            <div>
              <div class="text-subtitle1 text-weight-bold">
                {{ $t("admin_avatar_reports.page_title") }}
              </div>
              <div class="text-caption text-grey-7">
                {{ $t("admin.dashboard.avatar_reports_description") }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetPendingAvatarReportCount {
    avatarReports(status: PENDING, first: 1, page: 1) {
      paginatorInfo {
        total
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { useRouter } from "vue-router"
import { useAvatarReportsPendingCount } from "src/use/avatarReports"

const { push } = useRouter()
const { count: pendingReportsCount } = useAvatarReportsPendingCount()

function goToUsers() {
  push("/admin/users")
}

function goToPublications() {
  push({ name: "admin:publication:index" })
}

function goToAvatarReports() {
  push({ name: "admin:avatar_reports" })
}
</script>

<style scoped>
/* Make every card in a row match the tallest — when one card's
   description wraps to a second line at a given breakpoint, the
   others grow to meet it instead of looking ragged. */
.admin-card {
  height: 100%;
}
.admin-card:hover {
  background-color: #f5f5f5;
  transition: background-color 0.2s;
}
</style>
