<template>
  <h3 class="q-my-sm">{{ $t("record_of_review.title") }}</h3>
  <p>
    <i18n-t
      :keypath="`record_of_review.page_section.call_to_action`"
      tag="p"
      scope="global"
    >
      <template #link>
        <router-link
          clickable
          :to="{ name: 'account:record_of_review' }"
          class="text-primary"
          >{{ $t(`record_of_review.title`) }}</router-link
        >
      </template>
    </i18n-t>
  </p>
</template>
