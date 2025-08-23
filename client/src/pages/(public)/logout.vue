<template>
  <q-page class="flex-center flex q-pa-md">
    <div style="max-width: 400px">
      <q-banner>
        <template #avatar>
          <q-spinner color="primary" />
        </template>
        <p>{{ $t("auth.logging_out") }}</p>
      </q-banner>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useLogout } from "src/use/user"

const { logoutUser } = useLogout()
onMounted(() => {
  logoutUser()
})
</script>
