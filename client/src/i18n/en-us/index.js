// This is just an example,
// so you can safely delete all default props below

export default {
  buttons: {
    more_info: "More Info"
  },
  auth: {
    loginRequired: "You need to login to access that page.",
    login: "Login",
    logout: "Logout",
    register: "Register",
    register_action: "Create Account",
    register_login: "Return to Login",
    login_help: "login",
    password_help: "reset your password",
    fields: {
      email: "Email",
      name: "Name",
      username: "Username",
      password: "Password"
    },
    password_meter: {
      header: "Password Analysis",
      summary:
        "Your password scores {score} out of 4.  If would likely take <strong>{crack_time}</strong> for a bot to guess your password."
    },
    aria: {
      more_info_password: "Show password complexity details",
      show_password: "Show Password"
    },
    validation: {
      PASSWORD_NOT_COMPLEX: "Your password needs to be more complex.",
      PASSWORD_COMPLEX: "Your password is sufficiently complex.",
      EMAIL_INVALID: "Please enter a valid email address.",
      USERNAME_IN_USE: "Sorry, that username is not available",
      USERNAME_AVAILABLE: "This username is available",
      EMAIL_IN_USE:
        "This email is already registed. {break} You might want to {loginAction} or {passwordAction}."
    },
    failures: {
      CREATE_FORM_VALIDATION:
        "Oops, please correct the errors above and try again.",
      CREATE_FORM_INTERNAL: "Error processing result, please try again later.",
      LOGIN_FORM_VALIDATION: "Please correct the errors above and try again.",
      FAILURE_IDENTITY_NOT_FOUND: "Username and/or password is incorrect.",
      CREDENTIALS_INVALID: "Username and password combination is incorrect.",
      FAILURE_CREDENTIALS_MISSING: "No credentials supplied.",
      FAILURE_OTHER: "Unknown error while logging in.",
      UNKNOWN: "An unexpected error occurred."
    }
  },
  helpers: {
    OPTIONAL_FIELD: "{0} (optional)",
    REQUIRED_FIELD: "{0} is required."
  },
  header: {
    account_link: "My Account",
    dashboard: "My Dashboard",
    menu_button_aria: "Show/hide navigation sidebar"
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
