// This is just an example,
// so you can safely delete all default props below

export default {
  buttons: {
    more_info: "More Info",
    dashboard: "Dashboard",
    add: "Add",
  },
  lists: {
    move_up: 'Move {0} Up',
    move_down: 'Move {0} Down',
    edit: 'Edit {0}',
    save: 'Save',
    cancel: 'Cancel',
    add: 'Add',
    new: 'New {0}',
    default_item_name: 'Item'
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
        "This email is already registered. {break} You might want to {loginAction} or {passwordAction}."
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
    user_list: "All Users",
    account_link: "My Account",
    dashboard: "My Dashboard",
    menu_button_aria: "Show/hide navigation sidebar"
  },
  account: {
    failures: {
      VERIFY_TOKEN_INVALID:
        "This verification link is not valid for your account.",
      VERIFY_EMAIL_VERIFIED: "Email address has already been verified.",
      VERIFY_TOKEN_EXPIRED: "This verification link has expired."
    },
    email_verify: {
      send_failure_notify: "Email verification could not be sent: {errors}",
      send_success_notify:
        "Verification email has been resent to <strong>{email}</strong>",
      resend_button: "Resend Email",
      resend_button_loading: "Loading...",
      resend_button_success: "Email Sent",
      unverified_email_banner:
        "Your email is unverified.  Check your email and click the link to verify your account.",
      verification_success:
        "Congrats! You have successfully verified your email."
    },
    update: {
      success: "Account successfully updated.",
      update_form_validation: "Please correct errors and try again.",
      update_form_internal: "Error processing result. Please try again later.",
      unknown: "An unexpected error occurred while updating.",
    },

    header: "My Account",
    preview_link: "Preview Public Profile",
    sections: {
      basic: "Basic Information",
      security: "Security and Passwords",
      affiliations: "Affiliations",
      privacy: "Privacy"
    }
  },
  failures: {
    UNKNOWN_ERROR: "An unknown error has occurred.",
    FORBIDDEN_ROUTE: "You don't have permission to access that page."
  },
  general_failure: "Oops, there was an error."
};
