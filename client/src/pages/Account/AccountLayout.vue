<template>
  <q-page class="">
    <div class="row justify-center items-start content-start">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <q-card-section class="flex flex-center bg-blue-1">
          <avatar-block
            avatar-size="80px"
            :user="currentUser"
            class="text-center"
          />
        </q-card-section>
        <q-card-section class="q-pa-none">
          <q-list>
            <q-item>
              <q-item-section>
                Submissions Created
              </q-item-section>
              <q-item-section
                avatar
                class="text-primary text-bold"
              >
                ??
              </q-item-section>
            </q-item>
            <q-item>
              <q-item-section>
                Submissions Reviewed
              </q-item-section>
              <q-item-section
                avatar
                class="text-primary text-bold"
              >
                ??
              </q-item-section>
            </q-item>
            <q-item
              clickable
              class="text-center bg-blue-1"
            >
              <q-item-section>
                View Public Profile
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>
      <q-card class="col-sm-9 col-xs-12 no-shadow outline no-border-radius">
        <q-card-section class="q-pa-none">
          <q-card-section class="q-pa-none">
            <q-tabs
              class="bg-grey-2"
              active-color="primary"
              indicator-color="primary"
              align="left"
            >
              <q-route-tab
                to="/account/profile"
                exact
                label="Account"
              />
              <q-route-tab
                to="/account/connections"
                exact
                label="Profile"
              />
              <q-route-tab
                to="/account/privacy"
                exact
                label="?Privacy?"
              />
              <q-route-tab
                to="/account/security"
                exact
                label="?Security?"
              />
            </q-tabs>
          </q-card-section>
          <q-card-section class="q-pa-none">
            <router-view />
          </q-card-section>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script>
import { CURRENT_USER } from "src/graphql/queries";
import AvatarBlock from "src/components/molecules/AvatarBlock.vue";
export default {
  name: "AccountLayout",
  components: { AvatarBlock },
  apollo: {
    currentUser: {
      query: CURRENT_USER
    }
  }
};
</script>
