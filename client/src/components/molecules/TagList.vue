<template>
  <div class="q-col-gutter-md row">
    <q-input
      v-model.trim="form.addValue"
      outlined
      :label="$t('lists.new', [itemName])"
      class="col-md-5 col-12"
      @keydown.enter.prevent="addItem"
    >
      <template #after>
        <q-btn ref="addBtn" class="q-py-sm" @click="addItem">
          <q-icon name="add" /> {{ $t("lists.add") }}
        </q-btn>
      </template>
    </q-input>
    <div class="col-md-7 col-12">
      <q-chip
        v-for="(item, index) in value"
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
import { reactive } from "@vue/composition-api"
export default {
  name: "TagList",
  props: {
    itemName: {
      type: String,
      default: function () {
        return this.$t("lists.default_item_name")
      },
    },
    value: {
      type: Array,
      default: () => [],
    },
    allowDuplicates: {
      type: Boolean,
      default: false,
    },
  },
  setup(props, { emit }) {
    const form = reactive({
      addValue: "",
    })

    function remove(index) {
      emit("input", [
        ...props.value.slice(0, index),
        ...props.value.slice(index + 1),
      ])
    }

    function addItem() {
      if (!form.addValue.length) {
        return
      }
      if (!props.allowDuplicates && props.value.includes(form.addValue)) {
        form.addValue = ""
        return
      }
      emit("input", [...props.value, form.addValue])
      form.addValue = ""
    }

    return { remove, addItem, form }
  },
}
</script>
