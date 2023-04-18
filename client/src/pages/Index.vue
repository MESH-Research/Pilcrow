<template>
  <q-layout>
    <q-page-container>
      <q-page class="q-pa-md">
        <q-img
          :alt="$t('header.logo_alt')"
          src="/logo.png"
          style="width: 400px; aspect-ratio: 1"
          class="col-s-12 q-ma-lg"
        ></q-img>
        <div class="">
          <h1>{{ $t(`home.h1`) }}</h1>
          <p>{{ $t(`home.value_statement`) }}</p>
          <p>{{ $t(`home.editorial_statement.statement`) }}</p>
          <q-list tag="ul">
            <q-item tag="li">{{
              $t(`home.editorial_statement.peer_review`)
            }}</q-item>
            <q-item tag="li">{{
              $t(`home.editorial_statement.export`)
            }}</q-item>
            <q-item tag="li">{{
              $t(`home.editorial_statement.invite`)
            }}</q-item>
          </q-list>
          <p>{{ $t(`home.grant_statement.statement`) }}</p>
          <i18n path="home.open_source.statement" tag="p">
            <template v-slot:pilcrow_docs>
              <a href="https://ccr.meshresearch.dev">{{
                $t(`home.open_source.pilcrow_link`)
              }}</a>
            </template>
          </i18n>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
import i18n from "src/i18n"

export default {
  name: "PageIndex",
  components: { i18n },
}
</script>
