const routes = [
  {
    path: "/",
    component: () => import("layouts/PublicLayout.vue"),
    children: [
      { path: "", component: () => import("pages/Index.vue") },
      { path: "register", component: () => import("pages/RegisterPage.vue") },
      { path: "login", component: () => import("pages/LoginPage.vue") },
      { path: "logout", component: () => import("src/pages/LogoutPage.vue") },
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
        component: () => import("pages/DashboardPage.vue"),
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
        path: "publication/:id/setup/",
        component: () => import("src/layouts/Publication/SetupLayout.vue"),
        props: true,
        children: [
          {
            name: "publication:setup:basic",
            path: "basic",
            meta: {
              name: "Basic",
            },
            component: () =>
              import("src/pages/Publication/Setup/BasicPage.vue"),
          },
          {
            name: "publication:setup:users",
            path: "users",
            meta: {
              name: "Users",
            },
            component: () =>
              import("src/pages/Publication/Setup/UsersPage.vue"),
          },
          {
            name: "publication:setup:content",
            path: "content",
            meta: {
              name: "Page Content",
            },
            component: () =>
              import("src/pages/Publication/Setup/ContentPage.vue"),
          },
          {
            name: "publication:setup:criteria",
            meta: {
              name: "Style Criteria",
            },
            path: "criteria",
            component: () =>
              import("src/pages/Publication/Setup/CriteriaPage.vue"),
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
        name: "publication:index",
        component: () => import("pages/Publication/PublicationsIndexPage.vue"),
      },
      {
        name: "publication:home",
        path: "/publication/:id",
        component: () => import("pages/Publication/PublicationHomePage.vue"),
        props: true,
      },
      {
        path: "/submissions",
        component: () => import("src/pages/SubmissionsPage.vue"),
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
        name: "submission_review",
        path: "/submission/review/:id",
        component: () => import("src/pages/SubmissionReview.vue"),
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
