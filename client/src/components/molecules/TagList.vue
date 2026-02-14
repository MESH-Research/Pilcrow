<template>
  <div class="q-col-gutter-md row">
    <q-input
      v-model.trim="v$.addValue.$model"
      outlined
      :label="$t('lists.new', [$t(`${t}.label`)])"
      :error="v$.addValue.$error"
      data-cy="input_field"
      class="col-md-5 col-12"
      @keydown.enter.prevent="addItem"
    >
      <template #error>
        <error-field-renderer
          :errors="v$.addValue.$errors"
          :prefix="`${t}.errors`"
        />
      </template>
      <template #after>
        <q-btn
          ref="addBtn"
          class="q-py-sm"
          :disabled="v$.addValue.$error || v$.addValue.$model.length === 0"
          @click="addItem"
        >
          <q-icon name="add" /> {{ $t("lists.add") }}
        </q-btn>
      </template>
    </q-input>
    <div class="col-md-7 col-12" data-cy="tag_list">
      <q-chip
        v-for="(item, index) in modelValue"
        :key="index"
        removable
        @remove="remove(index)"
      >
        {{ item }}
      </q-chip>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from "vue"
import { useVuelidate, type ValidationRuleCollection } from "@vuelidate/core"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

interface Props {
  t?: string
  modelValue?: string[]
  rules?: ValidationRuleCollection
  allowDuplicates?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  t: "lists",
  modelValue: () => [],
  rules: () => ({}),
  allowDuplicates: false
})

const emit = defineEmits<{
  "update:modelValue": [value: string[]]
}>()

const form = reactive({
  addValue: ""
})

const noNewDuplicate = (value: string) => !props.modelValue.includes(value)
const noopRule = () => true
const vRules = {
  addValue: {
    ...props.rules,
    duplicate: props.allowDuplicates ? noopRule : noNewDuplicate
  }
}

const v$ = useVuelidate(vRules, form)

function remove(index: number) {
  emit("update:modelValue", [
    ...props.modelValue.slice(0, index),
    ...props.modelValue.slice(index + 1)
  ])
}

function addItem() {
  if (!form.addValue.length) {
    return
  }
  if (v$.value.addValue.$error) {
    return
  }
  emit("update:modelValue", [...props.modelValue, form.addValue])
  form.addValue = ""
}
</script>
