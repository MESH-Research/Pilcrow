import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"
import { DateTime } from "luxon"
import { computed } from "vue"

TimeAgo.addLocale(en)
TimeAgo.setDefaultLocale("en-US")

export function useTimeAgo() {
  return new TimeAgo("en-US")
}

export function relativeTime(date, style = "long") {
  const timeAgo = useTimeAgo()

  const isoDate = computed(() => {
    return date ? DateTime.fromISO(date) : undefined
  })

  return computed(() => {
    return isoDate.value ? timeAgo.format(isoDate.value.toJSDate(), style) : ""
  })
}
