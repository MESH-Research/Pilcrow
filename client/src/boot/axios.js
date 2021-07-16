import Vue from "vue";
import axios from "axios";
import { Cookies } from "quasar";

Vue.prototype.$axios = axios;

if (process.env.SERVER) {
  Vue.prototype.$axios.defaults.baseURL = "http://localhost:3001";
}

// you need access to `ssrContext`
export default function ({ ssrContext }) {
  if (process.env.SERVER) {
    var cookies = Cookies.parseSSR(ssrContext);
    var phpsession = cookies.get("PHPSESSID");
    Vue.prototype.$axios.defaults.withCredentials = true;
    Vue.prototype.$axios.defaults.headers.common[
      "Cookie"
    ] = `PHPSESSID=${phpsession}`;
  }

  // "cookies" is equivalent to the global import as in non-SSR builds
}
