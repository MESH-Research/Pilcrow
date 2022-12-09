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
      <a v-if="banner_link" :href="banner_link">{{
        $t("generic.more_info")
      }}</a>
    </div>
    <template #action>
      <q-btn
        icon="close"
        name="Dismiss for 1 week"
        flat
        dense
        @click="dismissBanner"
      />
      <q-tooltip>{{ $t("app_banner_dismiss_tip") }}</q-tooltip>
    </template>
  </q-banner>
</template>

<script setup>
import { useQuasar } from "quasar"
import { ref } from "vue"

const { localStorage } = useQuasar()
const sKey = "hideBannerUntil"
const hideBanner = ref(false)

const banner = process.env.APP_BANNER ?? null
const banner_class = process.env.APP_BANNER_CLASS ?? "bg-yellow-2 text-black"
const banner_link = process.env.APP_BANNER_LINK ?? null

if (localStorage.has(sKey)) {
  const until = localStorage.getItem(sKey)
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
