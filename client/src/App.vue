<template>
  <router-view />
</template>

<script>
import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"
TimeAgo.addDefaultLocale(en)

export default {
  name: "App",
}
</script>
