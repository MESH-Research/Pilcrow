<template>
  <q-footer bordered class="light-grey text-black">
    <q-toolbar class="flex flex-center text-caption">
      <div class="footer-text">
        <span>{{ $t(`footer.footer_text`) }}</span>
        <a href="https://github.com/MESH-Research/Pilcrow">{{
          $t(`footer.site_title`)
        }}</a>
        <span v-if="version">
          &nbsp;
          <component :is="versionUrl ? 'a' : 'span'" :href="versionUrl">
            {{ version }}
          </component>
          <q-tooltip v-if="parsedDate && parsedDate.isValid">
            {{ parsedDate.toFormat("dd-LLL-yyyy T") }} ({{ versionAge }})
          </q-tooltip>
        </span>
      </div>
    </q-toolbar>
  </q-footer>
</template>

<script setup lang="ts">
import { useTimeAgo } from "src/use/timeAgo"
import { onMounted, ref } from "vue"
import { type DateTimeMaybeValid } from "luxon"
import { DateTime } from "luxon"

const timeAgo = useTimeAgo()

const parsedDate = ref<DateTimeMaybeValid>()
const version = ref("")
const versionAge = ref("")
const versionUrl = ref("")
const versionDate = ref("")

onMounted(async () => {
  const response = await fetch("/version.json")
  const data = await response.json()
  version.value = data.version || ""
  versionUrl.value = data.versionUrl || ""
  versionDate.value = data.versionDate || ""
  parsedDate.value = versionDate.value
    ? DateTime.fromISO(versionDate.value)
    : undefined
  versionAge.value =
    parsedDate.value && !parsedDate.value.isValid
      ? timeAgo.format(parsedDate.value.toJSDate(), "round")
      : ""
})
</script>
