<template>
  <div v-if="dirName != 'pilcrow'" class="local-dev-banner">
    📂 {{ dirName }}
  </div>
</template>

<script setup lang="ts">
const appRoot = process.env.LANDO_APP_ROOT as string | undefined
const dirName = appRoot?.split("/").pop() ?? null
</script>

<style scoped lang="sass">
.local-dev-banner
  position: absolute
  top: 0
  left: 50%
  transform: translateX(-50%)
  background: rgb(0, 0, 0)
  color: rgba(255, 255, 255)
  font-size: 10px
  padding: 3px 8px
  border-radius: 0 0 4px 4px
  z-index: 1
  pointer-events: none
</style>
