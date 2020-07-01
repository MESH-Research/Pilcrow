// This is just an example,
// so you can safely delete all default props below

export default {
  auth: {
    login: "Login",
    logout: "Logout",
    register: "Register",
    login_fields: {
      username: "Username or Email",
      password: "Password"
    },
    failure: {
      FAILURE_IDENTITY_NOT_FOUND: "Username and/or password is incorrect.",
      FAILURE_CREDENTIALS_INVALID:
        "Username and password combination is incorrect.",
      FAILURE_CREDENTIALS_MISSING: "No credentials supplied.",
      FAILURE_OTHER: "Unknown error while logging in.",
      UNKNOWN: "An unexpected error occurred."
    }
  },
  header: {
    account_link: "My Account"
  },
  account: {
    header: "My Account",
    preview_link: "Preview Public Profile",
    sections: {
      basic: "Basic Information",
      security: "Security and Passwords",
      affiliations: "Affiliations",
      privacy: "Privacy"
    }
  }
};
