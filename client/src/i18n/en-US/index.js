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
    add: "Add",
    save: "Save",
    saving: "Saving",
    saved: "Saved",
    discard_changes: "Discard Changes",
  },
  lists: {
    move_up: "Move {0} Up",
    move_down: "Move {0} Down",
    delete: "Delete {0}",
    edit: "Edit {0}",
    save: "Save",
    add: "Add",
    new: "Add {0}",
    label: "Item",
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
        notComplex: "Your password needs to be more complex.",
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
  generic_validations: {
    maxLength: "The maximum length has been exceeded.",
  },
  header: {
    account_link: "My Account",
    dashboard: "My Dashboard",
    menu_button_aria: "Show/hide navigation sidebar",
    publications: "Publications",
    submissions: "Submissions",
    user_list: "All Users",
    notification_button: "View Notifications",
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
    account: {
      fields: {
        username: {
          label: "Username",
          hint: "Your username is your primary identity on this site.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
            USERNAME_IN_USE: "Sorry, this username is already in use.",
          },
        },
        name: {
          label: "Name",
          hint: "(Optional) Your name will often substitute appearances of your username on this site.  Honorifics accepted.",
          errors: {
            maxLength: "@:generic_validation.maxLength",
          },
        },
        email: {
          label: "Email Address",
          hint: "Updating your email address will require you to re-verify your account.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
            email: "Please enter a valid email address.",
          },
        },
        password: {
          label: "New Password",
          hint: "Enter a new password here. (Leave blank to keep your current password)",
          errors: {
            notComplex:
              "Your password is not complex enough.  Click (More Info) for suggestions.",
          },
        },
      },
    },
    profile: {
      section_details: "Profile Details",
      section_personal: "Personal Details",
      section_biography: "Biography",
      section_social_media: "Social Media Profiles",
      section_academic_profiles: "Academic Profiles",
      section_websites: "Websites",
      section_keywords: "Keywords",
      fields: {
        professional_title: {
          label: "Professional Title",
          errors: {
            maxLength: "@:generic_validations.maxLength",
          },
        },
        specialization: {
          label: "Specialization",
          hint: "Area of expertise, specialization or research focus.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
          },
        },

        affiliation: {
          label: "Affiliation",
          hint: "Institutional, group, or organization affiliation.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
          },
        },
        social_media: {
          twitter: {
            label: "Twitter",
            errors: {
              valid: "Please enter a valid Twitter handle.",
              maxLength: "@:generic_validations.maxLength",
            },
          },
          instagram: {
            label: "Instagram",
            errors: {
              valid: "Please enter a valid Instagram profile name.",
              maxLength: "@:generic_validations.maxLength",
            },
          },
          linkedin: {
            label: "LinkedIn",
            errors: {
              valid: "Please enter a valid LinkedIn permalink.",
              maxLength: "@:generic_validations.maxLength",
            },
          },
          facebook: {
            label: "Facebook",
            errors: {
              valid: "Please enter a valid Facebook profile name.",
              maxLength: "@:generic_validations.maxLength",
            },
          },
        },
        biography: {
          label: "Biography",
          maxLength: "@:generic_validations.maxLength",
        },
        academic_profiles: {
          academia_edu_id: {
            label: "Academia.edu",
            errors: {
              maxLength: "@:generic_validations.maxLength",
            },
          },
          humanities_commons: {
            label: "Humanities Commons",
            errors: {
              maxLength: "@:generic_validations.maxLength",
            },
          },
          orcid: {
            label: "ORCID",
            errors: {
              maxLength: "@:generic_validations.maxLength",
            },
          },
        },
        website: {
          label: "Website",
          errors: {
            maxLength: "@:generic_validations.maxLength",
            valid: "Please enter a valid URL.",
            duplicate: "This URL is already in the list",
          },
        },
        interest_keyword: {
          label: "Interest Keyword",
          hint: "Interest keywords will be used to help provide suggestions for submissions which may be of interest to you.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
            duplicate: "This keyword is already in the list.",
          },
        },
        disinterest_keyword: {
          label: "Disinterest Keyword",
          hint: "Disinterest keywords will be used to help filter suggestions for submissions that are not of interest to you.",
          errors: {
            maxLength: "@:generic_validations.maxLength",
            duplicate: "This keyword is already in the list.",
          },
        },
      },
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
    editors: {
      heading: "Editors",
      none: "An editor is not assigned to this publication.",
      unassign: {
        error: "An error occurred while attempting to unassign an editor.",
        success: "{display_name} successfully unassigned as an editor.",
      },
      assign: {
        duplicate:
          "{display_name} is already assigned as an editor for this publication.",
        error:
          "An error occurred while attempting to assign an editor. Is the user already assigned to this publication?",
        success: "{display_name} successfully assigned as an editor.",
      },
      unassign_button: {
        ariaLabel: "Unassign Editor",
        help: "Remove Editor",
      },
    },
    publication_admins: {
      heading: "Administrators",
      none: "An administrator is not assigned to this publication.",
      unassign: {
        error:
          "An error occurred while attempting to unassign an administrator.",
        success: "{display_name} successfully unassigned as an administrator.",
      },
      assign: {
        duplicate:
          "{display_name} is already assigned as an administrator for this publication.",
        error:
          "An error occurred while attempting to assign an editor. Is the user already assigned to this publication?",
        success: "{display_name} successfully assigned as an administrator.",
      },
      unassign_button: {
        ariaLabel: "Unassign Administator",
        help: "Remove Administrator",
      },
    },
    style_criteria: {
      heading: "Style Criteria",
      edit_button: "Edit",
      delete_confirm: "Are you sure you want to delete {name}?",
      delete_header: "Confirm Delete Criteria",
      addBtnLabel: "Add Criteria",
      saveError:
        "Oops, there was an error saving, check the form above for errors.",
      deleteError: "Oops, unable to delete.",
      fields: {
        name: {
          label: "Name",
          errors: {
            maxLength: "Name cannot be longer than 20 characters.",
            required: "Name is required.",
          },
        },
        description: {
          placeholder: "Enter an optional description for this style criteria",
          errors: {
            maxLength: "Description cannot be longer than 4096 characters.",
          },
        },
        icon: {
          tooltip: "Click to change icon.",
          ariaLabel: "Change icon.",
          search: "Search icons",
        },
      },
    },
  },
  submissions: {
    details_heading: "Submission Details",
    view_heading: "View Submission",
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
    review_coordinators: {
      heading: "Review Coordinator",
      none: "A review coordinator is not assigned to this submission.",
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
          "An error occurred while attempting to assign a review coordinator. Is the user already assigned to this submission?",
        success:
          "{display_name} successfully assigned as a review coordinator.",
      },
      unassign_button: {
        ariaLabel: "Unassign Review Coordinator",
        help: "Remove Review Coordinator",
      },
    },
    reviewers: {
      heading: "Reviewers",
      none: "No reviewers are assigned to this submission.",
      unassign: {
        error: "An error occurred while attempting to unassign a reviewer.",
        success: "{display_name} successfully unassigned as a reviewer.",
      },
      assign: {
        duplicate:
          "{display_name} is already assigned as a reviewer to this submission.",
        error:
          "An error occurred while attempting to assign a reviewer. Is the user already assigned to this submission?",
        success: "{display_name} successfully assigned as a reviewer.",
      },
      unassign_button: {
        ariaLabel: "Unassign Reviewer",
        help: "Remove Reviewer",
      },
    },
    submitters: {
      heading: "Submitters",
      title: {
        singular: "Submitter",
        plural: "Submitters",
      },
      none: "No submitter is assigned to this submission. At least one submitter must be assigned.",
      unassign_button: {
        ariaLabel: "Unassign Submitterr",
        help: "Remove Submitter",
      },
    },
    comment: {
      ariaLabel: "Comment. Author {username}. {replies} replies.",
      dateLabel: "Created {date}",
      reply: {
        ariaLabel: "Comment Reply.  Author {username}.",
        referenceButtonAria: "Jump to referenced comment",
        title: "Your Reply",
      },
      actions_btn_aria: "Comment Actions",
      placeholder: "Add a comment …",
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
    none: "There are no notifications.",
    view_more: "View More",
    dismiss_all: "Dismiss All",
    list: "List of Notifications",
    review: {
      requested: {
        short: "{user_username} has requested your review on {object_name}",
      },
      commentReplied: {
        short: "{user_username} has replied to your comment on {object_name}",
      },
    },
    submission: {
      created: {
        short:
          "{data_user_username} has submitted {data_submission_title} to {data_publication_name}",
      },
      approved: {
        short: "{object_name} has been approved",
      },
      resubmitted: {
        short: "{object_name} has been resubmitted",
      },
    },
  },
  guiElements: {
    feedbackMessage: {
      closeButton: "Close",
    },
    form: {
      submit: "Submit",
      cancel: "Cancel",
    },
    button: {
      bold: {
        ariaLabel: "Toggle bold on selected text",
        tooltipText: "Bold",
      },
      italic: {
        ariaLabel: "Toggle italics on selected text",
        tooltipText: "Italics",
      },
      bulletedList: {
        ariaLabel: "Toggle bulleted list",
        tooltipText: "Bulleted list",
      },
      numberedList: {
        ariaLabel: "Toggle numbered list",
        tooltipText: "Numbered list",
      },
      indent: {
        ariaLabel: "Indent list item",
        tooltipText: "Indent list item",
      },
      unindent: {
        ariaLabel: "Unindent list item",
        tooltipText: "Unindent list item",
      },
      link: {
        ariaLabel: "Insert a link",
        tooltipText: "Insert link",
      },
      unlink: {
        ariaLabel: "Unset a link",
        tooltipText: "Unset link",
      },
    },
  },
  dialog: {
    bypassStyleCriteria: {
      line_1: "Style criteria are an important part of {link}.",
      line_1_linktext: "Collaborative Community Review",
      line_2:
        "Are you sure you do not want to associate one with this comment?",
      action_submit: "Post comment as is",
      action_cancel: "Add style criteria",
    },
  },
}
