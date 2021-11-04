<template>
  <div>
    <draggable
      v-if="value.length"
      tag="div"
      :list="value"
      handle=".handle"
      ghost-class="ghost"
      :disabled="itemUnderEdit !== false"
      bordered
      separator
      :component-data="{ props: { bordered: true, separator: true } }"
    >
      <q-item v-for="(item, index) in value" :key="index">
        <q-item-section v-if="itemUnderEdit !== index" avatar>
          <q-icon
            name="reorder"
            class="handle"
            :disabled="itemUnderEdit !== false"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label
            v-if="itemUnderEdit !== index"
            class="ellipsis"
            @click="editItem(index)"
          >
            {{ item }}
          </q-item-label>
          <q-input
            v-else
            v-model.trim="v$.editItemValue.$model"
            :error="v$.editItemValue.$error"
            outlined
            @keydown.enter.prevent="saveEdit"
          >
            <template #error>
              <error-field-renderer
                :errors="v$.editItemValue.$errors"
                :prefix="`${t}.errors`"
              />
            </template>
            <template #after>
              <div class="q-gutter-sm">
                <q-btn
                  :aria-label="$t('lists.save', [$t(`${t}.label`)])"
                  dense
                  class="q-py-sm"
                  @click="saveEdit"
                >
                  <q-icon name="check" />
                </q-btn>
              </div>
            </template>
          </q-input>
        </q-item-section>
        <q-item-section
          v-if="itemUnderEdit !== index"
          side
          style="flex-direction: row; align-items: center"
        >
          <collapse-toolbar :collapse="$q.screen.lt.md">
            <q-btn
              flat
              dense
              icon="edit"
              :aria-label="$t('lists.edit', [$t(`${t}.label`)])"
              :disabled="itemUnderEdit !== false"
              @click="editItem(index)"
            />
            <q-btn
              :disabled="index === 0 || itemUnderEdit !== false"
              flat
              dense
              icon="arrow_upward"
              :aria-label="$t('lists.move_up', [$t(`${t}.label`)])"
              @click="reorderItem(index, -1)"
            />
            <q-btn
              :disabled="index === value.length - 1 || itemUnderEdit !== false"
              flat
              dense
              icon="arrow_downward"
              :aria-label="$t('lists.move_down', [$t(`${t}.label`)])"
              @click="reorderItem(index, 1)"
            />
            <q-btn
              flat
              dense
              icon="delete"
              :aria-label="$t('lists.delete', [$t(`${t}.label`)])"
              :disabled="itemUnderEdit !== false"
              @click="deleteItem(index)"
            />
          </collapse-toolbar>
        </q-item-section>
      </q-item>
    </draggable>
    <q-input
      v-model="v$.addItemValue.$model"
      :label="$t('lists.new', [$t(`${t}.label`)])"
      :error="v$.addItemValue.$error"
      outlined
      @keydown.enter.prevent="addItem"
    >
      <template v-if="inputIcon" #prepend>
        <q-icon :name="inputIcon" />
      </template>
      <template #error>
        <error-field-renderer
          :errors="v$.addItemValue.$errors"
          :prefix="`${t}.errors`"
        />
      </template>
      <template #after>
        <q-btn
          ref="addBtn"
          class="q-py-sm"
          :disabled="
            v$.addItemValue.$error || v$.addItemValue.$model.length === 0
          "
          @click="addItem"
        >
          <q-icon name="add" /> {{ $t("lists.add") }}
        </q-btn>
      </template>
    </q-input>
  </div>
</template>

<script>
import Draggable from "vuedraggable"
import CollapseToolbar from "./CollapseToolbar.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { ref, reactive } from "@vue/composition-api"
import useVuelidate from "@vuelidate/core"

export default {
  name: "EditableList",
  components: { Draggable, CollapseToolbar, ErrorFieldRenderer },
  props: {
    value: {
      type: Array,
      default: () => [],
    },
    inputIcon: {
      type: String,
      default: "",
    },
    rules: {
      type: Object,
      default: () => {},
    },
    t: {
      type: String,
      default: "lists",
    },
    allowDuplicates: {
      type: Boolean,
      default: false,
    },
  },
  setup(props, { emit }) {
    const itemUnderEdit = ref(false)

    const form = reactive({
      addItemValue: "",
      editItemValue: "",
    })

    const noNewDuplicate = (value) => !props.value.includes(value)
    const noExistingDuplicate = (value) => {
      const otherEntries = [
        ...props.value.slice(0, itemUnderEdit.value),
        ...props.value.slice(itemUnderEdit.value + 1),
      ]
      return !otherEntries.includes(value)
    }
    const noopRule = () => true
    const vRules = {
      addItemValue: {
        ...props.rules,
        duplicate: props.allowDuplicates ? noopRule : noNewDuplicate,
      },
      editItemValue: {
        ...props.rules,
        duplicate: props.allowDuplicates ? noopRule : noExistingDuplicate,
      },
    }
    const v$ = useVuelidate(vRules, form)

    function addItem() {
      if (!form.addItemValue.length) {
        return
      }
      if (v$.value.addItemValue.$error) {
        return false
      }

      emit("input", [...props.value, form.addItemValue])
      form.addItemValue = ""
    }
    function deleteItem(index) {
      emit("input", [
        ...props.value.slice(0, index),
        ...props.value.slice(index + 1),
      ])
    }
    function editItem(index) {
      if (form.editItemValue !== false) {
        saveEdit()
      }
      form.editItemValue = props.value[index]
      itemUnderEdit.value = index
    }
    function saveEdit() {
      const index = itemUnderEdit.value
      if (v$.value.editItemValue.$error) {
        return
      }
      if (
        index !== false &&
        index < props.value.length &&
        form.editItemValue.length &&
        !v$.value.editItemValue.$error
      ) {
        emit("input", [
          ...props.value.slice(0, index),
          form.editItemValue,
          ...props.value.slice(index + 1),
        ])
      }
      itemUnderEdit.value = false
      form.editItemValue = ""
    }
    function reorderItem(index, dir) {
      const newIndex = index + dir
      if (newIndex === props.value.length || newIndex < 0) {
        return
      }
      const startIndex = newIndex > index ? index : newIndex
      const values = props.value.slice(startIndex, startIndex + 2)
      values.reverse()
      emit("input", [
        ...props.value.slice(0, startIndex),
        ...values,
        ...props.value.slice(startIndex + 2),
      ])
    }

    return {
      reorderItem,
      saveEdit,
      editItem,
      deleteItem,
      addItem,
      v$,
      itemUnderEdit,
    }
  },
}
</script>

<style lang="sass" scoped>
.ghost
  opacity: 0.5
  background: #c8ebfb
.handle
  cursor: move
  color: #888
.q-btn-dropdown
  color: #000
</style>
