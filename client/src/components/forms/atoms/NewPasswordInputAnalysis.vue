<template>
  <div>
    <p v-html="$t('auth.password_meter.summary', { score, crack_time })" /> <!-- eslint-disable-line vue/no-v-html -->a
    <div
      v-if="suggestions.length"
      class="password-suggestions"
    >
      <q-list dense>
        <q-item
          v-if="warning.length"
          class="warning"
        >
          <q-item-section avatar>
            <q-icon
              class="text-red"
              name="warning"
            />
          </q-item-section>
          {{ warning }}
        </q-item>
        <q-item
          v-for="(message, index) in suggestions"
          :key="index"
          class="suggestion"
        >
          <q-item-section avatar>
            <q-icon
              color="primary"
              name="close"
            />
          </q-item-section>
          {{ message }}
        </q-item>
      </q-list>
    </div>
  </div>
</template>
<script>
export default {
  name: "NewPasswordInputAnalysis",
  props: {
    complexity: {
      type: Object,
      required: true
    }
  },
  computed: {
    suggestions() {
      return this.complexity.feedback.suggestions;
    },
    warning() {
      return this.complexity.feedback.warning;
    },
    score() {
      return this.complexity.score;
    },
    crack_time() {
      return this.complexity.crack_times_display
        .offline_slow_hashing_1e4_per_second;
    }
  }
};
</script>
<style lang="sass" scoped>
p:last-child
  margin: 0
</style>
