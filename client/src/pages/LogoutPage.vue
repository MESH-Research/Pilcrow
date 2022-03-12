<template>
  <p>Logging out...</p>
</template>

<script setup>
import { useLogout } from "src/use/user"
import { onMounted } from "vue"

const { logoutUser } = useLogout()
onMounted(() => {
  logoutUser()
})
</script>
