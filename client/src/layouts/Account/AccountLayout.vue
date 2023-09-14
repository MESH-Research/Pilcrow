<template>
  <div>
    <div class="row justify-center items-start content-start q-pa-md">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div v-if="currentUser" class="row">
          <q-card-section
            class="col-sm-12 col-xs-12 flex flex-center avatar-profile-block q-mt-none"
          >
            <avatar-block avatar-size="80px" :user="currentUser" />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
            <collapse-menu :items="items" />
          </q-card-section>
        </div>
      </q-card>
      <q-card class="col-sm-9 col-xs-12 no-shadow outline no-border-radius">
        <router-view />
      </q-card>
    </div>
  </div>
</template>

<script setup>
import AvatarBlock from "src/components/molecules/AvatarBlock.vue"
import CollapseMenu from "src/components/molecules/CollapseMenu.vue"
import { useCurrentUser } from "src/use/user"
import { useI18n } from "vue-i18n"
const { t } = useI18n()

const items = [
  {
    icon: "account_circle",
    label: t(`profile.page_title`),
    url: "/account/profile",
  },
  {
    icon: "o_settings",
    label: t(`settings.page_title`),
    url: "/account/settings",
  },
]

const { currentUser } = useCurrentUser()
</script>
