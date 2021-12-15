import { flatten } from "flat"

export default function getIcon(type) {
  const icons = flatten({
    submission: {
      created: "remove_red_eye",
    },
  })
  return icons[type] ?? "help"
}
