<template>
  <q-card bordered flat>
    <q-banner inline-actions>
      <div>{{ title }}</div>
      <div class="text-caption">
        <slot />
      </div>
      <template #avatar>
        <q-icon
          class="material-icons-outlined"
          :name="icon.name"
          :color="icon.color"
        />
      </template>
      <template #action>
        <q-btn
          v-if="!done"
          :label="$t(`submissions.create.todo.btn_label.go`)"
          @click="$emit('goClick')"
        />
        <q-btn
          v-if="$props.skipable"
          flat
          :label="$t(`submissions.create.todo.btn_label.skip`)"
          @click="$emit('skipClick')"
        />
        <q-btn
          v-if="done"
          flat
          :label="$t(`submissions.create.todo.btn_label.done`)"
          @click="$emit('goClick')"
        />
      </template>
    </q-banner>
  </q-card>
</template>

<script setup>
import { computed, defineProps, defineEmits } from "vue"
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  skipable: {
    type: Boolean,
    required: false,
    default: false,
  },
  done: {
    type: Boolean,
    required: false,
    default: false,
  },
})
defineEmits(["goClick", "skipClick"])

const icon = computed(() => {
  let n = "check_box_outline_blank"
  let c = ""
  if (props.skipable) {
    n = "skip_next"
  }
  if (props.done) {
    n = "check_box"
    c = "positive"
  }
  return {
    name: n,
    color: c,
  }
})
</script>

<style scoped></style>
