<template>
  <div>
    <div class="row justify-center items-start content-start q-pa-md">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div class="row">
          <q-card-section
            class="col-sm-12 col-xs-12 flex flex-center avatar-profile-block q-mt-none"
          >
            <avatar-block
              avatar-size="80px"
              :user="currentUser"
              class="text-center"
            />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
            <collapse-menu :items="items" />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-12 q-mt-md q-pa-none">
            <q-list>
              <q-item>
                <q-item-section> Submissions Created </q-item-section>
                <q-item-section avatar class="text-primary text-bold">
                  ??
                </q-item-section>
              </q-item>
              <q-item>
                <q-item-section> Submissions Reviewed </q-item-section>
                <q-item-section avatar class="text-primary text-bold">
                  ??
                </q-item-section>
              </q-item>
              <q-item clickable class="light-grey">
                <q-item-section avatar><q-icon name="launch" /></q-item-section>
                <q-item-section> Preview Public Profile </q-item-section>
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

const items = [
  {
    icon: "account_circle",
    label: "Account Information",
    url: "/account/profile",
  },
  {
    icon: "contact_page",
    label: "Profile Details",
    url: "/account/metadata",
  },
]

const { currentUser } = useCurrentUser()
</script>
