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
import CollapseMenu from "src/components/molecules/CollapseMenu.vue"
import { defineComponent } from "@vue/composition-api"
import { useQuery, useResult } from "@vue/apollo-composable"

export default defineComponent({
  name: "AccountLayout",
  components: { AvatarBlock, CollapseMenu },
  setup() {
    const { result } = useQuery(CURRENT_USER)
    const currentUser = useResult(result, {})

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
    return { currentUser, items }
  },
})
</script>
