import { maxLength } from "@vuelidate/validators"
import { required, helpers } from "@vuelidate/validators"
import { watch } from "vue"
import validator from "validator"

export const social_regex = {
  facebook: {
    url: /^(?:https?:)?\/\/(?:www\.)?(?:facebook|fb)\.com\/(?<profile>(?![A-Za-z]+\.php)(?!marketplace|gaming|watch|me|messages|help|search|groups)[A-Za-z0-9_\-.]+)\/?/,
    valid: /^(?<profile>[A-Za-z0-9_\-.]+)$/
  },

  twitter: {
    url: /(?:https?:)?\/\/(?:[A-Za-z]+\.)?twitter\.com\/@?(?!home|share|privacy|tos)(?<username>[A-Za-z0-9_]+)\/?/,
    valid: /^[A-Za-z0-9_]+$/
  },
  instagram: {
    url: /^(?:https?:)?\/\/(?:www\.)?(?:instagram\.com|instagr\.am)\/(?<username>[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$)$/,
    valid:
      /^[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$/
  },
  linkedin: {
    url: /^(?:https?:)?\/\/(?:[\w]+\.)?linkedin\.com\/in\/(?<permalink>[\w\-_À-ÿ%]+)\/?$/,
    valid: /^[\w\-_À-ÿ%]+$/
  }
}

const checkUrl = (value) => {
  if (value === "") {
    // After the user adds a website, this code prevents Vuelidate from making
    // the subsequent empty website input invalidate the form, causing an
    // error message to mistakenly appear, and prevents submitting the form.
    return true
  }
  return validator.isURL(value)
}

export const website_rules = {
  maxLength: maxLength(512),
  valid: checkUrl
}

const validWebsites = (value) => {
  return Array.isArray(value) ? value.every((v) => checkUrl(v)) : true
}

export const rules = {
  username: {
    required
  },
  name: {},
  profile_metadata: {
    position_title: { maxLength: maxLength(256) },
    specialization: { maxLength: maxLength(256) },
    affiliation: { maxLength: maxLength(256) },
    biography: { maxLength: maxLength(4096) },
    social_media: {
      facebook: {
        valid: helpers.regex(social_regex.facebook.valid),
        maxLength: maxLength(128)
      },
      twitter: {
        valid: helpers.regex(social_regex.twitter.valid),
        maxLength: maxLength(128)
      },
      instagram: {
        valid: helpers.regex(social_regex.instagram.valid),
        maxLength: maxLength(128)
      },
      linkedin: {
        valid: helpers.regex(social_regex.linkedin.valid),
        maxLength: maxLength(128)
      }
    },
    academic_profiles: {
      humanities_commons: { maxLength: maxLength(128) },
      orcid_id: { maxLength: maxLength(128) }
    },
    websites: {
      validWebsites
    }
  }
}

export const profile_defaults = {
  username: "",
  name: "",
  profile_metadata: {
    biography: "",
    position_title: "",
    specialization: "",
    affiliation: "",
    websites: [],
    social_media: {
      google: "",
      twitter: "",
      facebook: "",
      instagram: "",
      linkedin: ""
    },
    academic_profiles: {
      orcid_id: "",
      humanities_commons: ""
    }
  }
}

export function useSocialFieldWatchers(form) {
  watch(
    () => form.profile_metadata.social_media.facebook,
    (value) => {
      const matches = value.match(social_regex.facebook.url)
      if (matches && matches.groups.profile) {
        form.profile_metadata.social_media.facebook = matches.groups.profile
      }
    }
  )
  watch(
    () => form.profile_metadata.social_media.twitter,
    (value) => {
      const matches = value.match(social_regex.twitter.url)
      if (matches && matches.groups.username) {
        form.profile_metadata.social_media.twitter = matches.groups.username
      }
    }
  )
  watch(
    () => form.profile_metadata.social_media.instagram,
    (value) => {
      const matches = value.match(social_regex.instagram.url)
      if (matches && matches.groups.username) {
        form.profile_metadata.social_media.instagram = matches.groups.username
      }
    }
  )
  watch(
    () => form.profile_metadata.social_media.linkedin,
    (value) => {
      const matches = value.match(social_regex.linkedin.url)
      if (matches && matches.groups.permalink) {
        form.profile_metadata.social_media.linkedin = matches.groups.permalink
      }
    }
  )
}

export default {
  rules,
  social_regex,
  profile_defaults,
  website_rules,
  useSocialFieldWatchers
}
