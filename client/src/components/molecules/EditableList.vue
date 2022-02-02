<template>
  <div>
    <draggable
      v-if="modelValue.length"
      tag="q-list"
      :list="modelValue"
      handle=".handle"
      ghost-class="ghost"
      :disabled="itemUnderEdit !== false"
      bordered
      separator
      :item-key="(element) => element"
      :component-data="{ props: { bordered: true, separator: true } }"
    >
      <template #item="{ element, index }">
        <q-item>
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
              {{ element }}
            </q-item-label>
            <q-input
              v-else
              v-model.trim="v$.editItemValue.$model"
              :error="v$.editItemValue.$error"
              outlined
              :data-cy="`edit_input_${[
                $t(`${t}.label`).toLowerCase(),
              ]}_${index}`"
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
            <collapse-toolbar
              :collapse="$q.screen.lt.md"
              :data-cy="`collapse_toolbar_${index}`"
            >
              <q-btn
                flat
                dense
                icon="edit"
                :data-cy="`edit_btn_${[
                  $t(`${t}.label`).toLowerCase(),
                ]}_${index}`"
                :aria-label="$t('lists.edit', [$t(`${t}.label`)])"
                :disabled="itemUnderEdit !== false"
                @click="editItem(index)"
              />
              <q-btn
                :disabled="index === 0 || itemUnderEdit !== false"
                flat
                dense
                :data-cy="`arrow_upward_${[
                  $t(`${t}.label`).toLowerCase(),
                ]}_${index}`"
                icon="arrow_upward"
                :aria-label="$t('lists.move_up', [$t(`${t}.label`)])"
                @click="reorderItem(index, -1)"
              />
              <q-btn
                :disabled="
                  index === modelValue.length - 1 || itemUnderEdit !== false
                "
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
      </template>
    </draggable>
    <q-input
      v-model="v$.addItemValue.$model"
      :label="$t('lists.new', [$t(`${t}.label`)])"
      :error="v$.addItemValue.$error"
      outlined
      :data-cy="cyAttr"
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
import { ref, reactive } from "vue"
import useVuelidate from "@vuelidate/core"

export default {
  name: "EditableList",
  components: { Draggable, CollapseToolbar, ErrorFieldRenderer },
  props: {
    /**
     * Model property for list of items.
     */
    modelValue: {
      type: Array,
      default: () => [],
    },
    /**
     * Icon to prepend to input
     */
    inputIcon: {
      type: String,
      default: "",
    },
    /**
     * Vuelidate valdiation rules to apply to new and edited items
     */
    rules: {
      type: Object,
      default: () => {},
    },
    /**
     * Translation root to use for label, hint, etc.
     */
    t: {
      type: String,
      default: "lists",
    },
    /**
     * Set true to allow duplicates items in list.
     */
    allowDuplicates: {
      type: Boolean,
      default: false,
    },
    /**
     * Cypress attribute for data-cy element
     */
    cyAttr: {
      type: String,
      default: "",
    },
  },
  emits: ["update:modelValue"],
  setup(props, { emit }) {
    const itemUnderEdit = ref(false)

    const form = reactive({
      addItemValue: "",
      editItemValue: "",
    })

    const noNewDuplicate = (value) => !props.modelValue.includes(value)
    const noExistingDuplicate = (value) => {
      const otherEntries = [
        ...props.modelValue.slice(0, itemUnderEdit.value),
        ...props.modelValue.slice(itemUnderEdit.value + 1),
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
      if (v$.value.addItemValue.$invalid) {
        return false
      }

      emit("update:modelValue", [...props.modelValue, form.addItemValue])
      form.addItemValue = ""
    }
    function deleteItem(index) {
      /**
       * Emits input event on update of model value.
       */
      emit("update:modelValue", [
        ...props.modelValue.slice(0, index),
        ...props.modelValue.slice(index + 1),
      ])
    }
    function editItem(index) {
      if (form.editItemValue !== false) {
        saveEdit()
      }
      form.editItemValue = props.modelValue[index]
      itemUnderEdit.value = index
    }
    function saveEdit() {
      const index = itemUnderEdit.value
      if (v$.value.editItemValue.$error) {
        return
      }
      if (
        index !== false &&
        index < props.modelValue.length &&
        form.editItemValue.length &&
        !v$.value.editItemValue.$error
      ) {
        emit("update:modelValue", [
          ...props.modelValue.slice(0, index),
          form.editItemValue,
          ...props.modelValue.slice(index + 1),
        ])
      }
      itemUnderEdit.value = false
      form.editItemValue = ""
    }
    function reorderItem(index, dir) {
      const newIndex = index + dir
      if (newIndex === props.modelValue.length || newIndex < 0) {
        return
      }
      const startIndex = newIndex > index ? index : newIndex
      const values = props.modelValue.slice(startIndex, startIndex + 2)
      values.reverse()
      emit("update:modelValue", [
        ...props.modelValue.slice(0, startIndex),
        ...values,
        ...props.modelValue.slice(startIndex + 2),
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
