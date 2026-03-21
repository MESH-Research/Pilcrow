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
  font-size: 13px
  padding: 3px 8px
  border-radius: 0 0 4px 4px
  z-index: 9999
  pointer-events: none
  box-shadow: 0 0 1px 1px #fff, 0 0 0 2px #000
</style>
