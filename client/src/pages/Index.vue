<template>
  <q-layout>
    <q-page-container>
      <q-page>
        <q-img
          :alt="$t('header.logo_alt')"
          src="/logo.png"
          style="width: 500px; aspect-ratio: 1;"
        ></q-img>
        <h1>
          Cultivating openness, collaboration, and communtiy in peer review.
        </h1>
        <p>
          Pilcrow is an application that facilitates the peer review process for
          scholarly communications. It is founded on creating supportive
          experiences for authors, reviewers, and editors and provides an
          environment for a peer review practice with transparency,
          collegiality, and ongoing developmemtal conversations.
        </p>
        <p>On Pilcrow, editorial teams can:</p>
        <q-list tag="ul">
          <q-item tag="li"
            >Move a piece of scholarship from submission through peer
            review,</q-item
          >
          <q-item tag="li">Export submissions for publication.</q-item>
          <q-item tag="li"
            >Invite, manage, and correspond with authors and reviewers.</q-item
          >
        </q-list>
        <p>
          Pilcrow is a collaboration between
          <a href="https://www.meshresearch.net">Mesh Research</a> and the
          <a href="https://publicphilosophyjournal.org"
            >Public Philosophy Journal</a
          >
          at Michigan State University. The project is supported by the Andrew
          W. Mellon Foundation (Grant #G-1910-07239).
        </p>
        <p>
          Pilcrow is an open source project. Documentation can be found at
          <a href="https://ccr.meshresearch.dev/"
            >https://ccr.meshresearch.dev/</a
          >
        </p>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script>
export default {
  name: "PageIndex",
}
</script>
