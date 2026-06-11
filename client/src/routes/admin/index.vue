<template>
  <div class="q-px-lg">
    <h2>{{ $t("header.application_administration") }}</h2>
    <div class="row q-col-gutter-md q-pb-lg">
      <div
        v-for="card in cards"
        :key="String(card.name)"
        class="col-xs-12 col-sm-6 col-md-4"
      >
        <q-card
          flat
          bordered
          class="cursor-pointer admin-card"
          :data-cy="card.dataCy"
          @click="push(card.url)"
        >
          <q-card-section class="row items-center no-wrap q-gutter-md">
            <div class="relative-position">
              <q-icon :name="card.icon" size="xl" color="accent" />
              <q-badge
                v-if="card.badge"
                floating
                color="red"
                :label="card.badge"
                :data-cy="card.dataCy ? `${card.dataCy}_badge` : undefined"
              />
            </div>
            <div>
              <div class="text-subtitle1 text-weight-bold">
                {{ $t(card.label) }}
              </div>
              <div v-if="card.description" class="text-caption">
                {{ $t(card.description) }}
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
import { computed } from "vue"
import { useRouter } from "vue-router"
import { useNavigation } from "src/use/navigation"
import { useAvatarReportsPendingCount } from "src/use/avatarReports"

definePage({
  name: "admin:dashboard",
  // No `crumb` — the page heading already reads "Application
  // Administration" and the parent `routes/admin.vue` contributes
  // the "Administration" crumb, so a self-crumb would duplicate.
  // `requiresAppAdmin` comes from the parent layout.
  meta: {}
})

const { push } = useRouter()
const { childrenOf } = useNavigation()
const { count: pendingReportsCount } = useAvatarReportsPendingCount()

// Build the dashboard cards from the sibling admin sections that opt in
// via `meta.navigation`. slice -2 selects the admin layout (one level up
// from this index leaf) so we read *its* direct children — not an outer
// layout like MainLayout. Detail/drill-down routes (e.g. users/:id) are
// nested *inside* their section folder, so they're never direct children
// here and never surface as a tile. Adding a new admin section with a
// `navigation` block makes a card appear automatically — no edits here.
const children = childrenOf({ name: "admin:dashboard" }, -2)

const cards = computed(() =>
  children.value
    .filter((c) => c.meta.navigation)
    .map((c) => {
      const isAvatarReports = c.name === "admin:avatar_reports"
      return {
        name: c.name,
        label: c.label,
        description: c.meta.navigation?.description,
        icon: c.icon,
        url: c.url,
        // Avatar moderation is the only card with a live count badge; its
        // data-cy hooks keep the existing dashboard tests/selectors stable.
        dataCy: isAvatarReports ? "admin_card_avatar_reports" : undefined,
        badge:
          isAvatarReports && pendingReportsCount.value > 0
            ? String(pendingReportsCount.value)
            : undefined
      }
    })
)
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
