import { maxLength } from "@vuelidate/validators"
import { helpers } from "@vuelidate/validators"
import { weburl_regex } from "src/utils/regex-weburl"
export const social_regex = {
  facebook:
    /^(?:https?:)?\/\/(?:www\.)?(?:facebook|fb)\.com\/(?<profile>(?![A-z]+\.php)(?!marketplace|gaming|watch|me|messages|help|search|groups)[A-z0-9_\-.]+)\/?$/,
  twitter: /^[A-z0-9_]+$/,
  instagram:
    /^(?:https?:)?\/\/(?:www\.)?(?:instagram\.com|instagr\.am)\/(?<username>[A-Za-z0-9_](?:(?:[A-Za-z0-9_]|(?:\.(?!\.))){0,28}(?:[A-Za-z0-9_]))?$)$/,
  linkedin:
    /^(?:https?:)?\/\/(?:[\w]+\.)?linkedin\.com\/in\/(?<permalink>[\w\-_À-ÿ%]+)\/?$/,
}

export const rules = {
  professional_title: { maxLength: maxLength(256) },
  specialization: { maxLength: maxLength(256) },
  affiliation: { maxLength: maxLength(256) },
  biography: { maxLength: maxLength(4096) },
  social_media: {
    facebook: {
      valid: helpers.regex(social_regex.facebook),
      maxLength: maxLength(128),
    },
    twitter: {
      valid: helpers.regex(social_regex.twitter),
      maxLength: maxLength(128),
    },
    instagram: {
      valid: helpers.regex(social_regex.instagram),
      maxLength: maxLength(128),
    },
    linkedin: {
      valid: helpers.regex(social_regex.linkedin),
      maxLength: maxLength(128),
    },
  },
  academic_profiles: {
    academia_edu: { maxLength: maxLength(128) },
    humanities_commons: { maxLength: maxLength(128) },
    orcid: { maxLength: maxLength(128) },
  },
}

export const profile_defaults = {
  biography: "",
  professional_title: "",
  specialization: "",
  affiliation: "",
  websites: [],
  interest_keywords: [],
  disinterest_keywords: [],
  social_media: {
    google: "",
    twitter: "",
    facebook: "",
    instagram: "",
    linkedin: "",
  },
  academic_profiles: {
    orcid_id: "",
    academia_edu_id: "",
    humanities_commons: "",
  },
}

export const website_rules = {
  maxLength: maxLength(128),
  valid: helpers.regex(weburl_regex),
}
export const tag_rules = {
  maxLength: maxLength(128),
}

export default {
  rules,
  social_regex,
  profile_defaults,
  website_rules,
  tag_rules,
}
