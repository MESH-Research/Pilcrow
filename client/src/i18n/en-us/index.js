// This is just an example,
// so you can safely delete all default props below

export default {
  user: {
    self: "No users | User | Users",
    email: "Email",
    name: "Name",
    username: "Username",
    password: "Password",
    empty_name: "No display name",
    details_heading: "User Details",
  },
  role: {
    self: "No roles | Role | Roles",
    no_roles_assigned: "No Roles Assigned",
  },
  buttons: {
    more_info: "More Info",
    dashboard: "Dashboard",
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
      password: "Password",
    },
    password_meter: {
      header: "Password Analysis",
      summary:
        "Your password scores {score} out of 4.  If would likely take <strong>{crack_time}</strong> for a bot to guess your password.",
    },
    aria: {
      more_info_password: "Show password complexity details",
      show_password: "Show Password",
    },
    validation: {
      username: {
        required: "Username is required.",
        USERNAME_IN_USE: "Sorry, this username is not available.",
      },
      email: {
        required: "Email address is required.",
        email: "Please enter a valid email address.",
        EMAIL_IN_USE: "This email is already registered.",
        EMAIL_NOT_VALID: "Please enter a valid email address.",
        EMAIL_IN_USE_HINT:
          "You might want to {loginAction} or {passwordAction}.",
      },
      password: {
        required: "Password is required",
      },
      PASSWORD_NOT_COMPLEX: "Your password needs to be more complex.",
      PASSWORD_COMPLEX: "Your password is sufficiently complex.",
      USERNAME_AVAILABLE: "This username is available",
    },
    failures: {
      FORM_VALIDATION: "Oops, please correct the errors above and try again.",
      INTERNAL: "An unexpected error has occurred. Please try again later.",
      LOGIN_FORM_VALIDATION: "Please correct the errors above and try again.",
      FAILURE_IDENTITY_NOT_FOUND: "Username and/or password is incorrect.",
      CREDENTIALS_INVALID: "Username and password combination is incorrect.",
      FAILURE_CREDENTIALS_MISSING: "No credentials supplied.",
      FAILURE_OTHER: "Unknown error while logging in.",
      UNKNOWN: "An unexpected error occurred.",
    },
  },
  helpers: {
    OPTIONAL_FIELD: "{0} (optional)",
    REQUIRED_FIELD: "{0} is required.",
  },
  header: {
    account_link: "My Account",
    dashboard: "My Dashboard",
    menu_button_aria: "Show/hide navigation sidebar",
    publications: "Publications",
    submissions: "Submissions",
    user_list: "All Users",
  },
  account: {
    failures: {
      VERIFY_TOKEN_INVALID:
        "This verification link is not valid for your account.",
      VERIFY_EMAIL_VERIFIED: "Email address has already been verified.",
      VERIFY_TOKEN_EXPIRED: "This verification link has expired.",
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
        "Congrats! You have successfully verified your email.",
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
      privacy: "Privacy",
    },
  },
  publications: {
    details: "Publication Details",
    create: {
      success: "Publication successfully created.",
      failure: "An error occurred while attempting to create the publication.",
      required: "A name is required to create a publication.",
      maxLength: "The maximum length has been exceeded for the name.",
      duplicate_name: "Publication name already exists.",
    },
    editor: {
      none: "No editors are assigned to this publication.",
      unassign: {
        error: "An error occurred while attempting to unassign an editor.",
        success: "{display_name} successfully unassigned as an editor.",
      },
      assign: {
        duplicate: "That editor is already assigned to this publication.",
        error:
          "An error occurred while attempting to assign an editor. Is this user already assigned as an editor?",
        success: "{display_name} successfully assigned as an editor.",
      },
    },
  },
  submissions: {
    create: {
      success: "Submission successfully created.",
      failure: "An error occurred while attempting to create the submission.",
      title: {
        required: "A title is required to create a submission.",
        max_length: "The maximum length has been exceeded for the title.",
      },
      publication_id: {
        required: "A publication must be associated with a submission.",
      },
      submitter_user_id: {
        required: "A user must be associated with a submission as a submitter.",
      },
      file_upload: {
        required: "A file must be uploaded with a submission.",
      },
    },
    review_coordinator: {
      none: "No review coordinators are assigned to this submission.",
      unassign: {
        error:
          "An error occurred while attempting to unassign a review coordinator.",
        success:
          "{display_name} successfully unassigned as a review coordinator.",
      },
      assign: {
        duplicate:
          "{display_name} is already assigned as a review coordinator to this submission.",
        error:
          "An error occurred while attempting to assign a review coordinator. Is this user already assigned as a review coordinator?",
        success:
          "{display_name} successfully assigned as a review coordinator.",
      },
    },
    reviewer: {
      none: "No reviewers are assigned to this submission.",
      unassign: {
        error: "An error occurred while attempting to unassign a reviewer.",
        success: "{display_name} successfully unassigned as a reviewer.",
      },
      assign: {
        duplicate:
          "{display_name} is already assigned as a reviewer to this submission.",
        error:
          "An error occurred while attempting to assign a reviewer. Is this user already assigned as a reviewer?",
        success: "{display_name} successfully assigned as a reviewer.",
      },
    },
    submitter: {
      title: {
        singular: "Submitter",
        plural: "Submitters",
      },
      none: "No submitter is assigned to this submission. At least one submitter must be assigned.",
    },
  },
  failures: {
    UNKNOWN_ERROR: "An unknown error has occurred.",
    FORBIDDEN_ROUTE: "You don't have permission to access that page.",
  },
  general_failure: "Oops, there was an error.",
  loading: "Loading...",
  /*********************
   * Notifications follow the format of:
   * <type>: {
   *    short: "",
   *    long: "",
   * }
   */
  notifications: {
    review: {
      requested: {
        short: "{user.username} has requested your review on {object.name}",
      },
      commentReply: {
        short: "{user.username} has replied to your comment on {object.name}",
      },
    },
    submission: {
      new: {
        short:
          "{user.username} has submitted {object.name} to {object.publication.name}",
      },
      approved: {
        short: "{object.name} has been approved",
      },
      resubmission: {
        short: "{object.name} has been resubmitted",
      },
    },
  },
}
