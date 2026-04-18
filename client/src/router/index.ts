import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory
} from "vue-router"
import routes from "./routes"

export default function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === "history"
      ? createWebHistory
      : createWebHashHistory

  const Router = createRouter({
    scrollBehavior: (to, from, savedPosition) => {
      if (savedPosition) return savedPosition
      // Keep the current scroll position when only the query or hash
      // changed on the same route (e.g. filter / view-preference URL
      // sync); fully navigating pages still scroll to top.
      if (to.path === from.path) return false
      return { left: 0, top: 0 }
    },
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    history: createHistory(process.env.VUE_ROUTER_BASE)
  })

  return Router
}
