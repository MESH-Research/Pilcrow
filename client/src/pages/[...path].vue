<template>
  <div class="column fixed-center text-center">
    <p>
      <img src="~assets/sad.svg" style="width: 30vw; max-width: 150px" />
    </p>
    <p class="text-faded">{{ $t("failures.error_404.text") }}</p>

    <q-btn
      color="secondary"
      style="width: 200px"
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

<script lang="ts" setup>
definePage({
  name: "error403"
})

const { go } = useRouter()
function goBack() {
  go(-1)
}
</script>
