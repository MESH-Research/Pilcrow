// Augments the auto-generated RouteNamedMap so routes still declared
// manually in src/router/routes.ts remain addressable by their `name:`
// in `<router-link :to="{ name: ... }">` and `router.push()` without
// breaking type-checking. As pages migrate into src/routes/ (the
// file-based routing folder) their entries can drop from this list.
//
// RouteNamedMap[Name] needs each manual route's path and params
// declared so vue-router's typed resolve() accepts it. For most
// manual routes we don't lean on typed params in call-sites, so
// widening to `ParamValue<true>` (accepts anything) is enough.

import type { ParamValue, RouteRecordInfo } from "vue-router"

type GenericParams = Record<string, ParamValue<true>>

declare module "vue-router/auto-routes" {
  interface RouteNamedMap {
    error403: RouteRecordInfo<"error403", "/error403">
    "publication:index": RouteRecordInfo<"publication:index", "/publications">
    "publication:home": RouteRecordInfo<
      "publication:home",
      "/publication/:id",
      GenericParams,
      GenericParams
    >
    "publication:setup:basic": RouteRecordInfo<
      "publication:setup:basic",
      "/publication/:id/setup/basic",
      GenericParams,
      GenericParams
    >
    "publication:setup:content": RouteRecordInfo<
      "publication:setup:content",
      "/publication/:id/setup/content",
      GenericParams,
      GenericParams
    >
    "publication:setup:criteria": RouteRecordInfo<
      "publication:setup:criteria",
      "/publication/:id/setup/criteria",
      GenericParams,
      GenericParams
    >
    "publication:setup:users": RouteRecordInfo<
      "publication:setup:users",
      "/publication/:id/setup/users",
      GenericParams,
      GenericParams
    >
    user_details: RouteRecordInfo<
      "user_details",
      "/user/:id",
      GenericParams,
      GenericParams
    >
    "user_details:submissions": RouteRecordInfo<
      "user_details:submissions",
      "/user/:id/submissions",
      GenericParams,
      GenericParams
    >
    "admin:dashboard": RouteRecordInfo<"admin:dashboard", "/admin">
    "admin:publication:index": RouteRecordInfo<
      "admin:publication:index",
      "/admin/publications"
    >
    "admin:users": RouteRecordInfo<"admin:users", "/admin/users">
    "submission:create": RouteRecordInfo<
      "submission:create",
      "/publication/:id/create",
      GenericParams,
      GenericParams
    >
    "submission:content": RouteRecordInfo<
      "submission:content",
      "/submission/:id/content",
      GenericParams,
      GenericParams
    >
    "submission:details": RouteRecordInfo<
      "submission:details",
      "/submission/:id",
      GenericParams,
      GenericParams
    >
    "submission:draft": RouteRecordInfo<
      "submission:draft",
      "/submission/:id/draft",
      GenericParams,
      GenericParams
    >
    "submission:export": RouteRecordInfo<
      "submission:export",
      "/submission/:id/export",
      GenericParams,
      GenericParams
    >
    "submission:export:html": RouteRecordInfo<
      "submission:export:html",
      "/submission/:id/export/html",
      GenericParams,
      GenericParams
    >
    "submission:preview": RouteRecordInfo<
      "submission:preview",
      "/submission/:id/preview",
      GenericParams,
      GenericParams
    >
    "submission:review": RouteRecordInfo<
      "submission:review",
      "/submission/:id/review",
      GenericParams,
      GenericParams
    >
    "submission:view": RouteRecordInfo<
      "submission:view",
      "/submission/:id",
      GenericParams,
      GenericParams
    >
  }
}
