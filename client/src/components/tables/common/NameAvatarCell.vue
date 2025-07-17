<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section side>
        <avatar-image :user="scope.row" rounded />
      </q-item-section>

      <q-item-section>
        <q-item-label v-if="scope.row.name">
          {{ scope.row.name }}
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script setup>
import { defineProps } from "vue"
import AvatarImage from "/src/components/atoms/AvatarImage.vue"

defineProps({
  scope: {
    type: Object,
    required: true
  }
})
</script>
