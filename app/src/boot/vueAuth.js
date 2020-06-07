import auth from "@websanova/vue-auth";
import authBearer from "@websanova/vue-auth/dist/drivers/auth/bearer.esm";
import routerVueRouter from "@websanova/vue-auth/dist/drivers/router/vue-router.2.x.esm";
import axiosAuth from "src/patches/vueAuth/axios.1.x.esm";

export default ({ router, Vue }) => {
  Vue.router = router;
  Vue.use(auth, {
    auth: authBearer,
    http: axiosAuth,
    router: routerVueRouter,
    loginData: {
      url: "/api/login",
      method: "POST"
    },
    fetchData: {
      url: "/api/login",
      method: "GET"
    },
    refreshData: {
      url: "/api/login"
    },
    parseUserData: data => data.user
  });
};
