import NameAvatarCell, { type NameAvatarColumn } from "./NameAvatarCell.vue"
import type { QueryTableColumn } from "../QueryTable.vue"

/**
 * Base columns shared by admin user tables (users, beta-users).
 */
export const userBaseColumns: (QueryTableColumn | NameAvatarColumn)[] = [
  {
    name: "name",
    required: true,
    align: "left",
    field: (row) => row,
    component: NameAvatarCell,
    hideUsername: true,
    sortable: true
  },
  {
    name: "username",
    align: "left",
    field: "username",
    sortable: true
  },
  {
    name: "email",
    align: "left",
    field: "email",
    sortable: true
  }
]
