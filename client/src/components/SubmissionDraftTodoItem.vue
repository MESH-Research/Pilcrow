<template>
  <q-card bordered flat>
    <q-card-section class="q-pa-none">
      <q-banner inline-actions>
        <div>
          {{ title }}
        </div>
        <div class="text-caption">
          <slot />
          <q-chip
            :color="
              $props.required
                ? `negative`
                : $props.darkMode
                  ? `grey-10`
                  : `grey-3`
            "
            :text-color="$props.required ? `white` : ``"
            class="q-mx-none q-mb-none"
          >
            {{ $props.required ? "Required" : "Optional" }}
          </q-chip>
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
            v-if="done"
            data-cy="todo_preview_btn"
            color="accent"
            :label="$t(`submissions.create.todo.btn_label.preview`)"
            class="q-mr-sm"
            @click="$emit('previewClick')"
          />
          <q-btn
            v-if="done"
            data-cy="todo_content_btn"
            flat
            :label="$t(`submissions.create.todo.btn_label.edit`)"
            @click="$emit('contentClick')"
          />
        </template>
      </q-banner>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { defineProps, defineEmits } from "vue"
defineProps({
  title: {
    type: String,
    required: true
  },
  required: {
    type: Boolean,
    required: false,
    default: false
  },
  done: {
    type: Boolean,
    required: false,
    default: false
  },
  darkMode: {
    type: Boolean,
    required: false,
    default: false
  }
})
defineEmits(["contentClick", "previewClick"])
</script>

<style scoped></style>
