import Vue from "vue";

export function isLoggedIn(state) {
  return Object.keys(state.user).length !== 0;
}

export function user(state) {
  return state.user;
}
