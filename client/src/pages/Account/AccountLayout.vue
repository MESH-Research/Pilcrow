<template>
  <div>
    <div class="row">
      <h2 class="q-pl-lg">My Account</h2>
    </div>
    <div class="row justify-center items-start content-start">
      <q-card class="col-sm-3 col-xs-12 no-shadow no-border-radius">
        <div class="row">
          <q-card-section
            class="col-sm-12 col-xs-4 flex flex-center bg-blue-1 q-mt-md"
          >
            <avatar-block
              avatar-size="80px"
              :user="currentUser"
              class="text-center"
            />
          </q-card-section>
          <q-card-section class="col-sm-12 col-xs-8 q-pa-none q-mt-md">
            <q-list>
              <q-item clickable class="text-center bg-grey-2">
                <q-item-section> View Public Profile </q-item-section>
              </q-item>
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
