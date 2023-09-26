<template>
  <q-card bordered flat>
    <q-banner inline-actions>
      <div>{{ title }}</div>
      <div class="text-caption">
        <slot />
      </div>
      <template #action>
        <q-btn
          v-if="!done"
          data-cy="todo_go_btn"
          color="primary"
          :label="$t(`submissions.create.todo.btn_label.go`)"
          @click="$emit('contentClick')"
        />
        <q-btn
          v-if="$props.skipable"
          flat
          :label="$t(`submissions.create.todo.btn_label.skip`)"
          @click="$emit('skipClick')"
        />
        <q-btn
          v-if="done"
          color="accent"
          :label="$t(`submissions.create.todo.btn_label.preview`)"
          class="q-mr-sm"
          @click="$emit('previewClick')"
        />
        <q-btn
          v-if="done"
          data-cy="todo_done_btn"
          flat
          :label="$t(`submissions.create.todo.btn_label.edit`)"
          @click="$emit('contentClick')"
        />
      </template>
    </q-banner>
  </q-card>
</template>

<script setup>
import { defineProps, defineEmits } from "vue"
defineProps({
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
defineEmits(["contentClick", "previewClick", "skipClick"])
</script>

<style scoped></style>
