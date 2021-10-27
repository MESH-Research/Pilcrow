<template>
  <div>
    <div class="row justify-center items-start content-start q-pa-md">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div class="row">
          <q-card-section
            bordered
            class="
              col-sm-12 col-xs-4
              flex flex-center
              avatar-profile-block
              q-mt-none
            "
          >
            <avatar-block
              avatar-size="80px"
              :user="currentUser"
              class="text-center"
            />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-8 q-mt-md q-pa-none">
            <q-list bordered>
              <q-item to="/account/profile">
                <q-item-section avatar
                  ><q-icon name="account_circle"
                /></q-item-section>
                <q-item-section> Account Information </q-item-section>
              </q-item>
              <q-item to="/account/metadata">
                <q-item-section avatar>
                  <q-icon name="contact_page" />
                </q-item-section>
                <q-item-section> Profile Details</q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-8 q-mt-md q-pa-none">
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
              <q-item clickable class="bg-grey-4">
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

<script>
import { CURRENT_USER } from "src/graphql/queries"
import AvatarBlock from "src/components/molecules/AvatarBlock.vue"
export default {
  name: "AccountLayout",
  components: { AvatarBlock },
  apollo: {
    currentUser: {
      query: CURRENT_USER,
    },
  },
}
</script>
