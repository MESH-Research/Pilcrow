<template>
  <q-card-section
    class="bg-secondary text-white q-mx-md"
    :class="{ 'q-py-sm': !firstSection }"
  >
    <div><slot></slot></div>
  </q-card-section>
</template>

<script setup lang="ts">
interface Props {
  firstSection?: boolean
}
withDefaults(defineProps<Props>(), {
  firstSection: false
})
</script>
