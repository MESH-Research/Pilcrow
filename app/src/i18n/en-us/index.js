// This is just an example,
// so you can safely delete all default props below

export default {
  auth: {
    login: "Login",
    logout: "Logout",
    register: "Register",
    failures: {
      FAILURE_IDENTITY_NOT_FOUND: "That username or email is not registered.",
      FAILURE_CREDENTIALS_INVALID:
        "Username and password combination is incorrect.",
      FAILURE_CREDENTIALS_MISSING: "No credentials supplied.",
      FAILURE_OTHER: "Unknown error while logging in."
    }
  }
};
