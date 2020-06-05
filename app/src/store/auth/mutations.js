export const LOGIN_OK = (state, { user }) => {
  state.User = user;
  state.isLoggedIn = true;
};
