<template>
  <q-card v-if="hasAnything" flat bordered class="q-pa-none">
    <q-card-section class="q-py-sm q-px-md row items-center no-wrap">
      <q-icon name="badge" size="sm" class="q-mr-sm text-grey-7" />
      <span class="role-label">
        {{ $t("publication.manage.user_detail.profile") }}
      </span>
    </q-card-section>
    <q-separator />
    <q-card-section class="q-py-sm q-px-md">
      <dl class="profile-fields q-ma-none">
        <template v-for="field in scalarFields" :key="field.key">
          <dt>{{ field.label }}</dt>
          <dd :class="field.key === 'biography' ? 'biography-dd' : ''">
            <template v-if="field.key === 'biography'">
              <p
                :class="[
                  'q-ma-none biography',
                  biographyExpanded ? 'expanded' : ''
                ]"
                :title="field.value"
              >
                {{ field.value }}
              </p>
              <q-btn
                v-if="biographyIsLong"
                flat
                dense
                no-caps
                size="sm"
                class="q-mt-xs q-pl-none"
                :label="
                  biographyExpanded
                    ? $t('publication.manage.user_detail.bio_collapse')
                    : $t('publication.manage.user_detail.bio_expand')
                "
                @click="biographyExpanded = !biographyExpanded"
              />
            </template>
            <template v-else>{{ field.value }}</template>
          </dd>
        </template>

        <template v-for="group in linkGroups" :key="group.label">
          <dt :class="group.layout === 'pill' ? 'pill-row' : ''">
            {{ group.label }}
          </dt>
          <dd :class="group.layout === 'pill' ? 'pill-row' : ''">
            <template v-if="group.layout === 'pill'">
              <a
                v-for="link in group.links"
                :key="link.label + link.href"
                :href="link.href"
                target="_blank"
                rel="noopener"
                class="profile-link row items-center q-px-sm q-py-xs"
                :title="link.label"
              >
                <q-icon :name="link.icon" size="sm" class="q-mr-xs" />
                <span class="ellipsis">{{ link.display }}</span>
              </a>
            </template>
            <!-- Long-form URLs (personal websites) stack vertically
                 as plain anchors — a pill would either truncate the
                 URL unhelpfully or stretch wide enough to break the
                 card's two-column layout. -->
            <template v-else>
              <a
                v-for="link in group.links"
                :key="link.label + link.href"
                :href="link.href"
                target="_blank"
                rel="noopener"
                class="profile-website"
                :title="link.label"
              >
                <q-icon :name="link.icon" size="xs" class="q-mr-xs" />
                <span>{{ link.display }}</span>
              </a>
            </template>
          </dd>
        </template>
      </dl>
    </q-card-section>
  </q-card>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment userProfileCard on ProfileMetadata {
    position_title
    specialization
    affiliation
    biography
    websites
    social_media {
      google
      twitter
      facebook
      instagram
      linkedin
    }
    academic_profiles {
      orcid_id
      humanities_commons
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref } from "vue"
import { useI18n } from "vue-i18n"
import type { userProfileCardFragment } from "src/graphql/generated/graphql"

interface Props {
  profile: userProfileCardFragment | null | undefined
}
const props = defineProps<Props>()
const { t } = useI18n()

const biographyExpanded = ref(false)

// Line-clamp kicks in well before this threshold; the expand
// control only appears when the text genuinely overflows.
const BIO_CLAMP_CHARS = 280
const biographyIsLong = computed(
  () => (props.profile?.biography?.length ?? 0) > BIO_CLAMP_CHARS
)

interface ScalarField {
  key: string
  label: string
  value: string
}
interface ProfileLink {
  label: string
  icon: string
  href: string
  display: string
}
interface LinkGroup {
  label: string
  // `pill`: compact rounded chips (short handles / ids).
  // `list`: plain anchors stacked on their own lines (URLs that
  // can run long and shouldn't be truncated in a pill).
  layout: "pill" | "list"
  links: ProfileLink[]
}

// Strip protocol + trailing slash for a cleaner display label.
function compactUrl(url: string): string {
  return url.replace(/^https?:\/\//, "").replace(/\/$/, "")
}

function ensureHttps(url: string): string {
  if (/^https?:\/\//i.test(url)) return url
  return `https://${url}`
}

// Text fields rendered as label/value rows. Skip the ones that are
// null/empty — the label only appears when there's a value, so the
// admin can see exactly which profile fields the user has filled in.
const scalarFields = computed<ScalarField[]>(() => {
  const p = props.profile
  if (!p) return []
  const rows: Array<[string, string | null | undefined, string]> = [
    ["position_title", p.position_title, "profile_fields.position_title"],
    ["affiliation", p.affiliation, "profile_fields.affiliation"],
    ["specialization", p.specialization, "profile_fields.specialization"],
    ["biography", p.biography, "profile_fields.biography"]
  ]
  return rows
    .filter(([, v]) => !!v && v !== "")
    .map(([key, value, tKey]) => ({
      key,
      label: t(`publication.manage.user_detail.${tKey}`),
      value: value as string
    }))
})

// Link-style fields grouped by source (academic / social / websites),
// each with a translated heading so the admin can see which field
// family the value came from.
const linkGroups = computed<LinkGroup[]>(() => {
  const p = props.profile
  if (!p) return []
  const groups: LinkGroup[] = []

  // Academic profiles.
  const academic: ProfileLink[] = []
  if (p.academic_profiles?.orcid_id) {
    academic.push({
      label: t("publication.manage.user_detail.profile_fields.orcid"),
      icon: "fab fa-orcid",
      href: `https://orcid.org/${p.academic_profiles.orcid_id}`,
      display: p.academic_profiles.orcid_id
    })
  }
  if (p.academic_profiles?.humanities_commons) {
    academic.push({
      label: t(
        "publication.manage.user_detail.profile_fields.humanities_commons"
      ),
      icon: "school",
      href: `https://hcommons.org/members/${p.academic_profiles.humanities_commons}/`,
      display: `@${p.academic_profiles.humanities_commons}`
    })
  }
  if (academic.length) {
    groups.push({
      label: t("publication.manage.user_detail.profile_fields.academic"),
      layout: "pill",
      links: academic
    })
  }

  // Social media.
  const social: ProfileLink[] = []
  const sm = p.social_media
  if (sm?.twitter) {
    social.push({
      label: t("publication.manage.user_detail.profile_fields.twitter"),
      icon: "fab fa-x-twitter",
      href: `https://x.com/${sm.twitter.replace(/^@/, "")}`,
      display: `@${sm.twitter.replace(/^@/, "")}`
    })
  }
  if (sm?.linkedin) {
    social.push({
      label: t("publication.manage.user_detail.profile_fields.linkedin"),
      icon: "fab fa-linkedin",
      href: ensureHttps(sm.linkedin),
      display: compactUrl(sm.linkedin)
    })
  }
  if (sm?.facebook) {
    social.push({
      label: t("publication.manage.user_detail.profile_fields.facebook"),
      icon: "fab fa-facebook",
      href: ensureHttps(sm.facebook),
      display: compactUrl(sm.facebook)
    })
  }
  if (sm?.instagram) {
    social.push({
      label: t("publication.manage.user_detail.profile_fields.instagram"),
      icon: "fab fa-instagram",
      href: `https://instagram.com/${sm.instagram.replace(/^@/, "")}`,
      display: `@${sm.instagram.replace(/^@/, "")}`
    })
  }
  if (sm?.google) {
    social.push({
      label: t("publication.manage.user_detail.profile_fields.google"),
      icon: "fab fa-google",
      href: ensureHttps(sm.google),
      display: compactUrl(sm.google)
    })
  }
  if (social.length) {
    groups.push({
      label: t("publication.manage.user_detail.profile_fields.social"),
      layout: "pill",
      links: social
    })
  }

  // Personal / external websites.
  const websites = (p.websites ?? []).filter(
    (w): w is string => typeof w === "string" && w.length > 0
  )
  if (websites.length) {
    groups.push({
      label: t("publication.manage.user_detail.profile_fields.websites"),
      layout: "list",
      links: websites.map((w) => ({
        label: w,
        icon: "link",
        href: ensureHttps(w),
        display: compactUrl(w)
      }))
    })
  }

  return groups
})

const hasAnything = computed(
  () => scalarFields.value.length > 0 || linkGroups.value.length > 0
)
</script>

<style scoped>
.role-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
}
.body--dark .role-label {
  color: rgba(255, 255, 255, 0.72);
}
/* Two-column label/value grid. Labels collapse to a single line
   above the value at narrow widths so the card still breathes.
   `align-items: baseline` makes the label line up with the first
   line of the value — looks right for both plain text and pill
   chip rows (biography's first line is what anchors the label). */
.profile-fields {
  display: grid;
  grid-template-columns: max-content 1fr;
  column-gap: 16px;
  row-gap: 10px;
  align-items: baseline;
}
@media (max-width: 560px) {
  .profile-fields {
    grid-template-columns: 1fr;
    row-gap: 4px;
  }
  .profile-fields dd {
    margin-bottom: 8px;
  }
}
.profile-fields dt {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-weight: 600;
  color: rgba(0, 0, 0, 0.55);
  line-height: 1.45;
  padding-top: 2px;
}
/* Pill groups (Academic profiles, Social media) — pills are taller
   than the label text, so center-align both the dt and dd for
   those rows rather than the baseline default. */
.profile-fields dt.pill-row,
.profile-fields dd.pill-row {
  align-self: center;
  padding-top: 0;
}
.body--dark .profile-fields dt {
  color: rgba(255, 255, 255, 0.65);
}
.profile-fields dd {
  margin: 0;
  line-height: 1.45;
  overflow-wrap: anywhere;
}
.profile-fields dd.biography-dd {
  font-size: 0.95rem;
}
.profile-fields dd a.profile-link + a.profile-link {
  margin-top: 4px;
}
/* Biography clamp/expand — three lines by default, full text when
   the user asks for it. overflow-wrap keeps long URL-ish tokens
   from stretching the card. */
.biography {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  overflow-wrap: anywhere;
  word-break: break-word;
  line-height: 1.45;
}
.biography.expanded {
  display: block;
  -webkit-line-clamp: unset;
  -webkit-box-orient: horizontal;
  overflow: visible;
}
.profile-link {
  display: inline-flex;
  align-items: center;
  max-width: 280px;
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 9999px;
  text-decoration: none;
  color: inherit;
  font-size: 0.85rem;
  background: rgba(0, 0, 0, 0.02);
}
.profile-link:hover {
  background: rgba(0, 0, 0, 0.05);
  color: var(--q-primary);
}
.body--dark .profile-link {
  border-color: rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.04);
}
.body--dark .profile-link:hover {
  background: rgba(255, 255, 255, 0.08);
}
/* Long-form website anchors: stacked, plain styling, can wrap
   mid-URL. Kept distinct from the pill chips so hostnames like
   "scholar.google.com/citations?user=..." don't truncate. */
.profile-website {
  display: block;
  color: var(--q-primary);
  text-decoration: none;
  font-size: 0.9rem;
  overflow-wrap: anywhere;
  word-break: break-word;
  line-height: 1.3;
}
.profile-website + .profile-website {
  margin-top: 4px;
}
.profile-website:hover {
  text-decoration: underline;
}
</style>
