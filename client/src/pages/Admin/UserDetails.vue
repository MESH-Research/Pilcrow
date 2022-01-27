<template>
  <div v-if="!user" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el :label="$t('user.self', 2)" to="/admin/users" />
        <q-breadcrumbs-el :label="$t('user.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <div class="row justify-center q-px-lg">
      <h2 class="col-sm-12" data-cy="userDetailsHeading">
        {{ user.username }}
      </h2>
    </div>
    <div class="row q-pa-lg q-col-gutter-lg">
      <section class="col-sm-2 col-xs-12">
        <div class="row justify-center">
          <q-item-section
            top
            avatar
            class="col-sm-12 col-xs-2 q-mb-lg q-pr-none"
          >
            <avatar-image :user="user" rounded class="fit" />
          </q-item-section>
        </div>
      </section>
      <section class="col-sm-10 col-xs-12">
        <div class="row q-mb-sm">
          <div
            :class="`${
              $q.screen.width < 440
                ? 'hidden'
                : 'col-3 q-pr-lg text-right text--grey '
            }`"
          >
            {{ $t("user.username") }}
          </div>
          <div class="col">
            <q-icon name="person_outline" class="text--grey" />
            {{ user.username }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div
            :class="`${
              $q.screen.width < 440
                ? 'hidden'
                : 'col-3 q-pr-lg text-right text--grey '
            }`"
          >
            {{ $t("user.email") }}
          </div>
          <div class="col">
            <q-icon name="mail_outline" class="text--grey" />
            {{ user.email }}
          </div>
        </div>
        <div class="row q-mb-sm">
          <div
            :class="`${
              $q.screen.width < 440
                ? 'hidden'
                : 'col-3 q-pr-lg text-right text--grey '
            }`"
          >
            {{ $t("user.name") }}
          </div>
          <div class="col">
            <div v-if="user.name">
              <q-icon name="label_outline" class="text--grey" />
              {{ user.name }}
            </div>
            <div v-else>
              <q-icon name="o_do_disturb_on" class="text--grey" />
              <span class="text--grey text-weight-light">
                {{ $t("user.empty_name") }}
              </span>
            </div>
          </div>
        </div>
        <div class="row q-mt-lg">
          <div
            :class="`${
              $q.screen.width < 440
                ? 'col-12 text-left q-mb-sm'
                : 'col-3 text-right q-pr-lg'
            } text--grey`"
          >
            {{ $t("role.self", 2) }}
          </div>
          <div
            v-if="user.roles.length"
            data-roles="has_roles"
            :class="`${$q.screen.width < 440 ? 'col-12' : 'col'}`"
          >
            <div
              v-for="role in user.roles"
              :key="role.id"
              class="q-mb-sm text-weight-medium"
            >
              <q-icon
                v-if="
                  role.name === 'Application Administrator' ||
                  role.name === 'Publication Administrator'
                "
                name="manage_accounts"
              />
              <q-icon v-if="role.name === 'Editor'" name="o_book" />
              {{ role.name }}
            </div>
          </div>
          <div
            v-else
            data-roles="no_roles"
            :class="`${$q.screen.width < 440 ? 'col-12' : 'col'}`"
          >
            <q-icon name="o_do_disturb_on" class="text--grey" />
            <span class="text--grey text-weight-light">
              {{ $t("role.no_roles_assigned") }}
            </span>
          </div>
        </div>
      </section>
    </div>
  </article>
</template>

<script setup>
import { GET_USER } from "src/graphql/queries"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useQuery, useResult } from "@vue/apollo-composable"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_USER, { id: props.id })
const user = useResult(result)
</script>
