<template>
  <div>
    <p v-html="$t('auth.password_meter.summary', { score, crack_time })" />
    <div class="password-suggestions" v-if="suggestions.length">
      <q-list dense>
        <q-item class="warning" v-if="warning.length">
          <q-item-section avatar>
            <q-icon class="text-red" name="warning" />
          </q-item-section>
          {{ warning }}
        </q-item>
        <q-item
          class="suggestion"
          :key="index"
          v-for="(message, index) in suggestions"
        >
          <q-item-section avatar>
            <q-icon color="primary" name="close" />
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
      type: Object
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
