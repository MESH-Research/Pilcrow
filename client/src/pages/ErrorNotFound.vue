<template>
  <div
    class="fullscreen bg-blue text-white text-center q-pa-md flex flex-center"
  >
    <div>
      <div style="font-size: 30vh">
        {{ $t("failures.error_404.404") }}
      </div>

      <div class="text-h2" style="opacity: 0.4">
        {{ $t("failures.error_404.text") }}
      </div>

      <q-btn
        class="q-mt-xl"
        color="white"
        text-color="blue"
        unelevated
        to="/"
        :label="$t(`failures.error_404.home`)"
        no-caps
      />
    </div>
  </div>
</template>

<script>
import { defineComponent } from "vue"

export default defineComponent({
  name: "ErrorNotFound"
})
</script>
