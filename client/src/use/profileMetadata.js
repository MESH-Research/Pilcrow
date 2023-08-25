import { maxLength } from "@vuelidate/validators"
import { required, helpers } from "@vuelidate/validators"
import { weburl_regex } from "src/utils/regex-weburl"
import { watch } from "vue"
export const social_regex = {
  facebook: {
    url: /^(?:https?:)?\/\/(?:www\.)?(?:facebook|fb)\.com\/(?<profile>(?![A-Za-z]+\.php)(?!marketplace|gaming|watch|me|messages|help|search|groups)[A-Za-z0-9_\-.]+)\/?/,
    valid: /^(?<profile>[A-Za-z0-9_\-.]+)$/,
  },

  twitter: {
    url: /(?:https?:)?\/\/(?:[A-Za-z]+\.)?twitter\.com\/@?(?!home|share|privacy|tos)(?<username>[A-Za-z0-9_]+)\/?/,
    valid: /^[A-Za-z0-9_]+$/,
  },
  instagram: {
    url: /^(?:https?:)?\/\/(?:www\.)?(?:instagram\.com|instagr\.am)\/(?<username>[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$)$/,
    valid:
      /^[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$/,
  },
  linkedin: {
    url: /^(?:https?:)?\/\/(?:[\w]+\.)?linkedin\.com\/in\/(?<permalink>[\w\-_À-ÿ%]+)\/?$/,
    valid: /^[\w\-_À-ÿ%]+$/,
  },
}

export const website_rules = {
  maxLength: maxLength(128),
  valid: helpers.regex(weburl_regex),
}
export const keyword_rules = {
  maxLength: maxLength(128),
}

const validWebsites = (value) =>
  Array.isArray(value)
    ? value.every((v) => v.match(weburl_regex) !== null)
    : true

export const rules = {
  username: {
    required,
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
        maxLength: maxLength(128),
      },
      twitter: {
        valid: helpers.regex(social_regex.twitter.valid),
        maxLength: maxLength(128),
      },
      instagram: {
        valid: helpers.regex(social_regex.instagram.valid),
        maxLength: maxLength(128),
      },
      linkedin: {
        valid: helpers.regex(social_regex.linkedin.valid),
        maxLength: maxLength(128),
      },
    },
    academic_profiles: {
      humanities_commons: { maxLength: maxLength(128) },
      orcid: { maxLength: maxLength(128) },
    },
    websites: {
      validWebsites,
    },
  }
}

export const profile_defaults = {
  username: "",
  name: "",
  profile_metadata : {
    biography: "",
    position_title: "",
    specialization: "",
    affiliation: "",
    websites: [],
    interest_keywords: [],
    social_media: {
      google: "",
      twitter: "",
      facebook: "",
      instagram: "",
      linkedin: "",
    },
    academic_profiles: {
      orcid_id: "",
      humanities_commons: "",
    },
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
  keyword_rules,
  useSocialFieldWatchers,
}
