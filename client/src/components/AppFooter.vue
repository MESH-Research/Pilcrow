<template>
  <q-footer bordered class="light-grey text-black">
    <q-toolbar class="flex flex-center text-caption">
      <div class="footer-text">
        <span>{{ $t(`footer.footer_text`) }}</span>
        <a href="https://github.com/MESH-Research/Pilcrow">{{ $t(`footer.site_title`) }}</a>
        <span v-if="version">
          &nbsp;
          <component :is="version_url ? 'a' : 'span'" :href="version_url">
            {{ version }}
          </component>
          <q-tooltip v-if="parsedDate && !parsedDate.invalid">
            {{ parsedDate.toFormat("dd-LLL-yyyy T") }} ({{ version_age }})
          </q-tooltip>
        </span>
      </div>
    </q-toolbar>
  </q-footer>
</template>

<script setup>
import { useTimeAgo } from "src/use/timeAgo"
import { DateTime } from "luxon"

const timeAgo = useTimeAgo()

const version = process.env.VERSION
const version_url = process.env.VERSION_URL
const version_date = process.env.VERSION_DATE

const parsedDate = version_date ? DateTime.fromISO(version_date) : undefined
const version_age =
  parsedDate && !parsedDate.invalid
    ? timeAgo.format(parsedDate.toJSDate(), "long")
    : undefined
</script>
