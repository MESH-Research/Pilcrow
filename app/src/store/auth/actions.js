import axios from "axios";

export function fetch({ commit }) {
  return new Promise((resolve, reject) => {
    axios.get("/auth/user").then(res => {
      commit("LOGIN_OK", res.data.user);
    }, reject);
  });
}

export function logout({ commit }) {
  return new Promise((resolve, reject) => {
    axios.get("/auth/logout").then(
      res => {
        commit("LOGOUT");
      },
      error => reject(error.response)
    );
  });
}
export function login({ commit }, { credentials }) {
  var formData = new FormData();
  formData.set("username", credentials.username);
  formData.set("password", credentials.password);

  return new Promise((resolve, reject) => {
    axios
      .post("/auth/login", formData)
      .then(res => {
        var user = res.data.user;

        commit("LOGIN_OK", user);
        resolve(res.data);
      })
      .catch(error => {
        reject(error.response.data);
      });
  });
}

export function register({ dispatch }, data) {
  data = data || {};

  return new Promise((resolve, reject) => {});
}
