const routes = [
  {
    path: "/",
    component: () => import("layouts/PublicLayout.vue"),
    children: [
      { path: "", component: () => import("pages/Index.vue") },
      { path: "register", component: () => import("pages/Register.vue") },
      { path: "login", component: () => import("pages/Login.vue") },
    ],
  },
  {
    path: "/",
    component: () => import("layouts/MainLayout.vue"),
    meta: { requiresAuth: true },
    children: [
      {
        path: "verify-email/:expires/:token",
        component: () => import("pages/VerifyEmail.vue"),
      },
      {
        path: "dashboard/",
        component: () => import("pages/Dashboard.vue"),
      },
      {
        path: "account/",
        component: () => import("src/layouts/Account/AccountLayout.vue"),
        children: [
          {
            path: "profile",
            component: () => import("src/pages/Account/ProfilePage.vue"),
          },
          {
            path: "metadata",
            component: () => import("src/pages/Account/MetadataPage.vue"),
          },
        ],
      },
      {
        path: "feed/",
        component: () => import("src/pages/FeedPage.vue"),
      },
      {
        path: "/admin/users",
        component: () => import("pages/Admin/UsersIndex.vue"),
        meta: { requiresRoles: ["Application Administrator"] },
      },
      {
        name: "user_details",
        path: "/admin/user/:id",
        props: true,
        component: () => import("pages/Admin/UserDetails.vue"),
      },
      {
        path: "/admin/publications",
        component: () => import("src/pages/Admin/PublicationsPage.vue"),
        meta: {
          requiresRoles: ["Application Administrator"],
        },
      },
      {
        path: "/publications",
        component: () => import("src/pages/PublicationsPage.vue"),
      },
      {
        name: "publication_details",
        path: "/publication/:id",
        component: () => import("pages/Admin/PublicationDetails.vue"),
        meta: {
          requiresRoles: [
            "Application Administrator",
            "Publication Administrator",
            "Editor",
          ],
        },
        props: true,
      },
      {
        path: "/submissions",
        component: () => import("src/pages/SubmissionPage.vue"),
      },
      {
        name: "submission_details",
        path: "/submission/:id",
        component: () => import("src/pages/SubmissionDetails.vue"),
        meta: {
          requiresSubmissionAccess: true,
        },
        props: true,
      },
      {
        name: "submission_view",
        path: "/submission/view/:id",
        component: () => import("src/pages/SubmissionView.vue"),
        meta: {
          requiresSubmissionAccess: true,
        },
        props: true,
      },
    ],
  },
  {
    path: "/error403",
    name: "error403",
    component: () => import("src/pages/Error403Page.vue"),
  },
]

// Always leave this as last one

routes.push({
  path: "/:catchAll(.*)*",
  component: () => import("src/pages/Error404Page.vue"),
})

export default routes
