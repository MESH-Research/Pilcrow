<template>
  <div class="column fixed-center text-center">
    <p>
      <img src="~assets/sad.svg" style="width: 30vw; max-width: 150px" />
    </p>
    <p class="text-faded">Sorry, nothing here...<strong>(404)</strong></p>

    <q-btn
      color="secondary"
      style="width: 200px"
      label="Go back"
      class="q-my-sm"
      icon="arrow_back"
      @click="goBack()"
    />

    <q-btn
      color="secondary"
      to="/"
      label="Go Home"
      class="q-my-sm"
      icon="home"
    />
  </div>
</template>

<script>
export default {
  methods: {
    goBack() {
      this.$router.go(-1)
    },
  },
}
</script>
