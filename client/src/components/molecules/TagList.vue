<template>
  <div class="q-col-gutter-md row">
    <q-input
      v-model.trim="v$.addValue.$model"
      outlined
      :label="$t('lists.new', [$t(`${t}.label`)])"
      :error="v$.addValue.$error"
      :data-cy="`add_${cyAttr}`"
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
    <div class="col-md-7 col-12" :data-cy="`tag_list_${cyAttr}`">
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

<script>
import { reactive } from "vue"
import { useVuelidate } from "@vuelidate/core"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

export default {
  name: "TagList",
  components: { ErrorFieldRenderer },
  props: {
    /**
     * Translation prefix for label, hint, errors, etc
     */
    t: {
      type: String,
      default: "lists",
    },
    /**
     * Model value, list of items
     */
    modelValue: {
      type: Array,
      default: () => [],
    },
    /**
     * Vuelidate rules to apply to new and edited items.
     */
    rules: {
      type: Object,
      default: () => {},
    },
    /**
     * Set true to allow duplicate items to be added to the list.
     */
    allowDuplicates: {
      type: Boolean,
      default: false,
    },
    /**
     * Cypress data-cy attribute value.
     */
    cyAttr: {
      type: String,
      default: "keywords",
    },
  },
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const form = reactive({
      addValue: "",
    })

    const noNewDuplicate = (value) => !props.modelValue.includes(value)
    const noopRule = () => true
    const vRules = {
      addValue: {
        ...props.rules,
        duplicate: props.allowDuplicates ? noopRule : noNewDuplicate,
      },
    }

    const v$ = useVuelidate(vRules, form)

    function remove(index) {
      /**
       * Emits input method on update of model value
       */
      emit("update:modelValue", [
        ...props.modelValue.slice(0, index),
        ...props.modelValue.slice(index + 1),
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

    return { remove, addItem, v$ }
  },
}
</script>
