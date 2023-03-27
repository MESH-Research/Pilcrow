import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"

TimeAgo.addDefaultLocale(en)

export function useTimeAgo() {
  return new TimeAgo("en-US")
}