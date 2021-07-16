<template>
  <div class="column fixed-center text-center">
    <p>
      <q-icon name="lock" style="font-size: 30vmin" color="negative" />
    </p>
    <p class="text-faded">Oops, you don't have permission to view this page.</p>

    <q-btn
      color="secondary"
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
  name: "Error404",
  methods: {
    goBack() {
      this.$router.go(-1)
    },
  },
}
</script>
