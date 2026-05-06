<template>
  <q-banner
    v-if="banner && !hideBanner"
    inline-actions
    dense
    class="justify-center text-center text-weight-bold"
    :class="banner_class"
  >
    <div>
      {{ banner }}
      <a v-if="banner_link" :href="banner_link" class="text-primary">{{
        $t("generic.more_info")
      }}</a>
    </div>
    <template #action>
      <q-btn
        icon="close"
        :aria-label="$t('app_banner_dismiss_tip')"
        flat
        dense
        @click="dismissBanner"
      />
      <q-tooltip>{{ $t("app_banner_dismiss_tip") }}</q-tooltip>
    </template>
  </q-banner>
</template>

<script setup lang="ts">
import { useQuasar } from "quasar"
import { ref } from "vue"

const { localStorage } = useQuasar()
const sKey = "hideBannerUntil"
const hideBanner = ref(false)

interface AppBannerConfig {
  text?: string | null
  class?: string | null
  link?: string | null
}
declare global {
  interface Window {
    __APP_BANNER?: AppBannerConfig
  }
}
const cfg: AppBannerConfig = window.__APP_BANNER ?? {}
const banner = cfg.text || null
const banner_class = cfg.class || "bg-yellow-2 text-black"
const banner_link = cfg.link || null

if (localStorage.has(sKey)) {
  const until = localStorage.getItem(sKey) as number
  if (until < Date.now()) {
    localStorage.remove(sKey)
  } else {
    hideBanner.value = true
  }
}

function dismissBanner() {
  hideBanner.value = true
  const oneWeekInMS = 1000 * 60 * 60 * 24 * 7
  localStorage.set(sKey, Date.now() + oneWeekInMS)
}
</script>
