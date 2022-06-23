<template>
  <div data-cy="vueDashboard">
    <q-banner v-if="currentUser" inline-actions class="text-white bg-positive">
      Welcome<span v-if="currentUser.name">, {{ currentUser.name }}</span
      >. You are logged in.
      <template #avatar>
        <q-icon name="dashboard" color="white" />
      </template>
      <template #action>
        <q-btn @click="logout"> Logout </q-btn>
      </template>
    </q-banner>
    <h2 class="q-px-lg">My Dashboard</h2>
  </div>
</template>

<script setup>
import { useCurrentUser, useLogout } from "src/use/user"
import { ref, onMounted } from "vue"
const { currentUser } = useCurrentUser()
const { logoutUser: logout } = useLogout()

const mounted = ref(false)
onMounted(() => {
  mounted.value = true
})
</script>
