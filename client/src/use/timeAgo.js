import TimeAgo from "javascript-time-ago"
import en from "javascript-time-ago/locale/en.json"

TimeAgo.addLocale(en)
TimeAgo.setDefaultLocale("en-US")

export function useTimeAgo() {
  return new TimeAgo("en-US")
}