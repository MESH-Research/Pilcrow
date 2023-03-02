<template>
  <q-page data-cy="vueDashboard" class="q-pa-lg">
    <section
      class="row wrap justify-start q-col-gutter-md items-stretch content-start"
    >
      <div class="col-md-6 col-xs-12">
        <q-card v-if="currentUser" flat bordered class="full-height">
          <q-card-section class="text-h4">
            <avatar-image
              :user="currentUser"
              rounded
              size="md"
              class="q-mr-sm"
            />
            Welcome<span v-if="currentUser.name">, {{ currentUser.name }}</span>
          </q-card-section>
          <q-separator />
          <q-card-actions align="evenly">
            <q-btn icon="o_settings" to="/account/profile"
              >Account Information</q-btn
            >
            <q-btn icon="o_contact_page" to="/account/metadata" padding="xs md"
              >Profile Details</q-btn
            >
            <q-btn icon="mdi-logout" @click="logout">Logout</q-btn>
          </q-card-actions>
        </q-card>
      </div>
      <div class="col-md-6 col-xs-12">
        <q-card
          flat
          bordered
          class="flex justify-center items-center full-height q-pa-md text-center"
        >
          <q-card-section class="text-h3">
            New to Pilcrow? Learn more in our
            <a href="https://docs.pilcrow.lndo.site" class="text-accent"
              >guide</a
            >.
          </q-card-section>
        </q-card>
      </div>
    </section>
    <h1 class="text-h2">My Dashboard</h1>
  </q-page>
</template>

<script setup>
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useCurrentUser, useLogout } from "src/use/user"

const { currentUser } = useCurrentUser()
const { logoutUser: logout } = useLogout()
</script>

<style lang="scss" scoped>
.q-btn {
  &::v-deep {
    .q-icon {
      margin-right: 0.5rem;
    }
  }
}
</style>
