<template>
  <q-page padding>
    <q-card flat bordered>
      <q-toolbar>
        <q-toolbar-title class="q-ml-xl q-my-md">{{
          $t("account.header")
        }}</q-toolbar-title>
      </q-toolbar>
      <q-separator />

      <q-card-section class="q-pa-none" :horizontal="!$q.screen.lt.md">
        <q-card-section
          class="q-pa-none"
          :class="{ 'col-md-1': '$q.screen.gte.md' }"
        >
          <q-tabs
            class="bg-grey-2"
            active-color="primary"
            indicator-color="primary"
            align="justify"
            :vertical="!$q.screen.lt.md"
          >
            <q-route-tab
              to="/account/profile"
              exact
              icon="person"
              label="Profile"
            />
            <q-route-tab
              to="/account/connections"
              exact
              icon="school"
              label="Connect"
            />
            <q-route-tab
              to="/account/privacy"
              exact
              icon="visibility"
              label="Privacy"
            />
            <q-route-tab
              to="/account/security"
              exact
              icon="lock"
              label="Security"
            />
          </q-tabs>
        </q-card-section>
        <q-card-section class="col-md-11 q-pa-none">
          <router-view></router-view>
        </q-card-section>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
export default {
  // name: 'PageName',
};
</script>
