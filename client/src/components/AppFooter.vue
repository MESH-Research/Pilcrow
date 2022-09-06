<template>
  <q-footer bordered class="bg-grey-4 text-black">
    <q-toolbar class="flex flex-center text-caption">
      <div>
        Powered by
        <a href="https://github.com/MESH-Research/CCR"
          >Collaborative Community Review (CCR)</a
        >
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
import TimeAgo from "javascript-time-ago"
import { DateTime } from "luxon"

const timeAgo = new TimeAgo("en-US")

const version = process.env.VERSION
const version_url = process.env.VERSION_URL
const version_date = process.env.VERSION_DATE

const parsedDate = version_date ? DateTime.fromISO(version_date) : undefined
console.log(parsedDate)
const version_age =
  parsedDate && !parsedDate.invalid
    ? timeAgo.format(parsedDate.toJSDate(), "long")
    : undefined
</script>
