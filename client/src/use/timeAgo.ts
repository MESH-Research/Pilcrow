import TimeAgo from "javascript-time-ago"
import type { FormatStyleName, Style } from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"
import { DateTime } from "luxon"
import { computed } from "vue"

TimeAgo.addLocale(en)
TimeAgo.setDefaultLocale("en-US")

// javascript-time-ago accepts label style names like "long" at runtime,
// but FormatStyleName doesn't include them. Widen the accepted type.
type StyleParam = FormatStyleName | Style | "long" | "short" | "narrow"

export function useTimeAgo() {
  const ta = new TimeAgo("en-US")
  return {
    format(date: Date | number, style?: StyleParam): string {
      return ta.format(date, style as FormatStyleName | Style) as string
    }
  }
}

export function relativeTime(date: string, style: StyleParam = "round") {
  const timeAgo = useTimeAgo()

  const isoDate = computed(() => {
    return date ? DateTime.fromISO(date) : undefined
  })

  return computed(() => {
    return isoDate.value ? timeAgo.format(isoDate.value.toJSDate(), style) : ""
  })
}
