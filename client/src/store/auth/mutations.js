export const LOGIN_OK = (state, user) => {
  state.user = Object.assign({}, user);
};

export const LOGOUT = state => {
  state.user = {};
};
