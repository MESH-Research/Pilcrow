import Vue from "vue";

export function user() {
  return Vue.auth.user();
}

export function impersonating() {
  return Vue.auth.impersonating();
}

export function isLoggedIn() {
  return Vue.auth.check();
}
