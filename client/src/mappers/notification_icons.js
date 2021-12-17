import { flatten } from "flat"

const icons = flatten({
  submission: {
    created: "content_copy",
  },
})

export default function getIcon(type) {
  return icons[type] ?? "help"
}
