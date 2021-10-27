<template>
  <div>
    <draggable
      v-if="value.length"
      tag="q-list"
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
            v-model.trim="editItemValue"
            outlined
            @keydown.enter.prevent="saveEdit"
          >
            <template #after>
              <div class="q-gutter-sm">
                <q-btn
                  :aria-label="$t('lists.save', [itemName])"
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
              :aria-label="$t('lists.edit', [itemName])"
              :disabled="itemUnderEdit !== false"
              @click="editItem(index)"
            />
            <q-btn
              :disabled="index === 0 || itemUnderEdit !== false"
              flat
              dense
              icon="arrow_upward"
              :aria-label="$t('lists.move_up', [itemName])"
              @click="reorderItem(index, -1)"
            />
            <q-btn
              :disabled="index === value.length - 1 || itemUnderEdit !== false"
              flat
              dense
              icon="arrow_downward"
              :aria-label="$t('lists.move_down', [itemName])"
              @click="reorderItem(index, 1)"
            />
            <q-btn
              flat
              dense
              icon="delete"
              :aria-label="$t('lists.delete', [itemName])"
              :disabled="itemUnderEdit !== false"
              @click="deleteItem(index)"
            />
          </collapse-toolbar>
        </q-item-section>
      </q-item>
    </draggable>
    <q-input
      v-model="addItemValue"
      :label="$t('lists.new', [itemName])"
      outlined
      @keydown.enter.prevent="addItem"
    >
      <template v-if="inputIcon" #prepend>
        <q-icon :name="inputIcon" />
      </template>
      <template #after>
        <q-btn ref="addBtn" class="q-py-sm" @click="addItem">
          <q-icon name="add" /> {{ $t("lists.add") }}
        </q-btn>
      </template>
    </q-input>
  </div>
</template>

<script>
import draggable from "vuedraggable"
import CollapseToolbar from "./CollapseToolbar.vue"

export default {
  name: "EditableList",
  components: { draggable, CollapseToolbar },
  props: {
    value: {
      type: Array,
      default: () => [],
    },
    inputIcon: {
      type: String,
      default: "",
    },
    itemName: {
      type: String,
      default: function () {
        return this.$t("lists.default_item_name")
      },
    },
    allowDuplicates: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      addItemValue: "",
      editItemValue: "",
      itemUnderEdit: false,
    }
  },
  methods: {
    addItem() {
      if (!this.addItemValue.length) {
        return
      }
      if (!this.allowDuplicates && this.value.includes(this.addItemValue)) {
        this.addItemValue = ""
        return
      }
      this.$emit("input", [...this.value, this.addItemValue])
      this.addItemValue = ""
    },
    deleteItem(index) {
      this.$emit("input", [
        ...this.value.slice(0, index),
        ...this.value.slice(index + 1),
      ])
    },
    editItem(index) {
      if (this.editItemValue !== false) {
        this.saveEdit()
      }
      this.editItemValue = this.value[index]
      this.itemUnderEdit = index
    },
    saveEdit() {
      const index = this.itemUnderEdit
      if (
        index !== false &&
        index < this.value.length &&
        this.editItemValue.length
      ) {
        this.$emit("input", [
          ...this.value.slice(0, index),
          this.editItemValue,
          ...this.value.slice(index + 1),
        ])
      }
      this.itemUnderEdit = false
      this.editItemValue = ""
    },
    reorderItem(index, dir) {
      const newIndex = index + dir
      if (newIndex === this.value.length || newIndex < 0) {
        return
      }
      const startIndex = newIndex > index ? index : newIndex
      const values = this.value.slice(startIndex, startIndex + 2)
      values.reverse()
      this.$emit("input", [
        ...this.value.slice(0, startIndex),
        ...values,
        ...this.value.slice(startIndex + 2),
      ])
    },
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
