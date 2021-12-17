import { flatten } from "flat"

export default function getIcon(type) {
  const icons = flatten({
    submission: {
      created: "content_copy",
    },
  })
  return icons[type] ?? "help"
}
