<template>
  <div class="column fixed-center text-center">
    <p>
      <q-icon name="lock" style="font-size: 30vmin" color="negative" />
    </p>
    <p class="text-faded">{{ $t("failures.error_403") }}</p>

    <q-btn
      color="secondary"
      :label="$t(`failures.error_404.back`)"
      class="q-my-sm"
      icon="arrow_back"
      @click="goBack()"
    />

    <q-btn
      color="secondary"
      to="/"
      :label="$t(`failures.error_404.home`)"
      class="q-my-sm"
      icon="home"
    />
  </div>
</template>

<script lang="ts">
export default {
  methods: {
    goBack() {
      this.$router.go(-1)
    }
  }
}
</script>
