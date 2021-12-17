import { flatten } from "flat"

const icons = flatten({
  submission: {
    created: "remove_red_eye",
  },
})

export default function getIcon(type) {
  return icons[type] ?? "help"
}
