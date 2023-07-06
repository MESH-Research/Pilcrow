<template>
  <div>
    <div class="row justify-center items-start content-start q-pa-md">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div v-if="currentUser" class="row">
          <q-card-section
            class="col-sm-12 col-xs-12 flex flex-center avatar-profile-block q-mt-none text-body1"
          >
            <avatar-block
              avatar-size="80px"
              :user="currentUser"
              class="text-body1"
            />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none text-body1">
            <collapse-menu :items="items" />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none text-body1">
            <q-list role="list">
              <q-item>
                <q-item-section> {{ $t(`account.submissions_created`) }} </q-item-section>
                <q-item-section
                  avatar
                  class="text-primary text-bold user-stats"
                >
                  ??
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section> {{ $t(`account.submissions_reviewed`) }} </q-item-section>
                <q-item-section
                  avatar
                  class="text-primary text-bold user-stats"
                >
                  ??
                </q-item-section>
              </q-item>
              <q-item clickable class="light-grey">
                <q-item-section avatar><q-icon name="launch" /></q-item-section>
                <q-item-section> {{ $t(`account.preview_link`) }} </q-item-section>
              </q-item>
            </q-list>
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
    label: t(`account.profile.account_information`),
    url: "/account/profile",
  },
  {
    icon: "contact_page",
    label: t(`account.profile.section_details`),
    url: "/account/metadata",
  },
]

const { currentUser } = useCurrentUser()
</script>
