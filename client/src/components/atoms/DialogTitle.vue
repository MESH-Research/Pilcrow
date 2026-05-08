<template>
  <div class="row no-wrap items-center bg-accent text-white">
    <q-card-section v-if="icon">
      <q-avatar
        :icon="icon"
        :color="avatarColor"
        :text-color="avatarTextColor"
      />
    </q-card-section>
    <q-card-section class="col text-center">
      <p class="q-ma-none" style="font-size: 1.25rem; line-height: 1.2">
        <slot />
      </p>
    </q-card-section>
  </div>
  <q-separator v-if="!noSeparator" />
</template>

<script setup lang="ts">
interface Props {
  icon?: string
  avatarColor?: string
  avatarTextColor?: string
  noSeparator?: boolean
}

withDefaults(defineProps<Props>(), {
  icon: undefined,
  avatarColor: "white",
  avatarTextColor: "accent",
  noSeparator: false
})
</script>
