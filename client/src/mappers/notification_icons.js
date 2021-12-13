import { flatten } from "flat"

export default function getIcon(type) {
  const icons = flatten({
    submission: {
      create: "remove_red_eye",
    },
  })
  return icons[type] ?? "help"
}
