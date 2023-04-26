<template>
  <q-layout data-cy="vueIndex">
    <q-page-container>
      <q-page >
        <div class="row items-center justify-center">
          <q-img
            :alt="$t('header.logo_alt')"
            src="/logo.png"
            style="width: 400px; aspect-ratio: 1"
            class="gt-md col-xs-12 col-sm-6 col-grow q-mr-lg"
          ></q-img>
          <div class="col-10 col-md-6">
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
            <i18n-t keypath="home.grant_statement.statement" tag="p">
              <template #mesh_url>
                <a href="https://www.meshresearch.net" class="text-primary">{{
                  $t(`home.grant_statement.mesh_link`)
                }}</a>
              </template>
              <template #ppj_url>
                <a
                  href="https://publicphilosophyjournal.org"
                  class="text-primary"
                  >{{ $t(`home.grant_statement.ppj_link`) }}</a
                >
              </template>
            </i18n-t>

            <i18n-t keypath="home.open_source.statement" tag="p">
              <template #pilcrow_docs>
                <a href="https://ccr.meshresearch.dev" class="hy-link"
                  >https://ccr.meshresearch.dev</a
                >
              </template>
            </i18n-t>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>
