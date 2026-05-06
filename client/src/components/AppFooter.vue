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
          <q-tooltip v-if="parsedDate && !parsedDate.invalid">
            {{ parsedDate.toFormat("dd-LLL-yyyy T") }} ({{ versionAge }})
          </q-tooltip>
        </span>
      </div>
    </q-toolbar>
  </q-footer>
</template>

<script setup lang="ts">
import { useTimeAgo } from "src/use/timeAgo"
import { computed } from "vue"
import { DateTime } from "luxon"

const timeAgo = useTimeAgo()

const version = process.env.VERSION ?? ""
const versionUrl = process.env.VERSION_URL ?? ""
const versionDate = process.env.VERSION_DATE ?? ""

const parsedDate = computed(() =>
  versionDate ? DateTime.fromISO(versionDate) : undefined
)
const versionAge = computed(() =>
  parsedDate.value && !parsedDate.value.invalid
    ? timeAgo.format(parsedDate.value.toJSDate(), "long")
    : ""
)
</script>
