import Vue from "vue";

export function fetch(data) {
  return Vue.auth.fetch(data);
}

export function refresh(data) {
  return Vue.auth.refresh(data);
}

export function login(ctx, data) {
  data = data || {};

  return new Promise((resolve, reject) => {
    Vue.auth
      .login({
        data: data.credentials
      })
      .then(res => {
        /*if (data.remember) {
              Vue.auth.remember(JSON.stringify({
                  name: ctx.getters.user.first_name
              }));
          }

          Vue.router.push({
              name: ctx.getters.user.type + '-landing'
          }); */

        resolve(res);
      }, reject);
  });
}

export function register({ dispatch }, data) {
  data = data || {};

  return new Promise((resolve, reject) => {
    Vue.auth
      .register({
        url: "auth/register",
        body: this.form.body, // VueResource
        data: this.form.body, // Axios
        autoLogin: false
      })
      .then(res => {
        if (data.autoLogin) {
          dispatch("login", data).then(resolve, reject);
        }
      }, reject);
  });
}

export function impersonate(ctx, data) {
  var props = this.getters["properties/data"];

  Vue.auth.impersonate({
    url: "auth/" + data.user.id + "/impersonate",
    redirect: "user-account"
  });
}

export function unimpersonate(ctx) {
  Vue.auth.unimpersonate({
    redirect: "admin-users"
  });
}

export function logout(ctx) {
  return Vue.auth.logout();
}
