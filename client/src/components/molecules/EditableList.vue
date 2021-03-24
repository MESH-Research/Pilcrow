<template>
  <div>
    <draggable
      v-if="value.length"
      tag="q-list"
      :list="value"
      handle=".handle"
      ghost-class="ghost"
      bordered
      separator
      :component-data="{props: {bordered: true, separator: true}}"
    >
      <q-item
        v-for="item, index in value"
        :key="index"
      >
        <q-item-section
          avatar
        >
          <q-icon
            name="reorder"
            class="handle"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label
            v-if="itemUnderEdit !== index"
            @click="editItem(index)"
          >
            {{ item }}
          </q-item-label>
          <q-input
            v-else
            v-model.trim="editItemValue"
            outlined
          >
            <template
              #after
            >
              <div
                class="q-gutter-sm"
              >
                <q-btn
                  :aria-label="$t('lists.save', [itemName]) "
                  dense
                  class="q-py-sm"
                  @click="saveEdit"
                >
                  <q-icon name="check" />
                </q-btn>
                <q-btn
                  dense
                  :aria-label="$t('lists.cancel', [itemName])"
                  class="q-py-sm"
                  @click="cancelEdit"
                >
                  <q-icon name="close" />
                </q-btn>
              </div>
            </template>
          </q-input>
        </q-item-section>
        <q-item-section
          side
          style="flex-direction: row; align-items: center"
        >
          <q-btn
            flat
            dense
            icon="edit"
            :aria-label="$t('lists.edit', [itemName])"
            @click="editItem(index)"
          />
          <q-btn
            :disabled="
              index
                ===
                0"
            flat
            dense
            icon="arrow_upward"
            :aria-label="$t('lists.move_up', [itemName])"
            @click="reorderItem(index, -1)"
          />
          <q-btn
            :disabled="
              index === value.length - 1"
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
            @click="deleteItem(index)"
          />
        </q-item-section>
      </q-item>
    </draggable>
    <q-input
      v-model="addItemValue"
      :label="$t('lists.new', [itemName])"
      outlined
      @keydown.enter.prevent="addItem"
    >
      <template
        v-if="inputIcon"
        #prepend
      >
        <q-icon :name="inputIcon" />
      </template>
      <template #after>
        <q-btn
          ref="addBtn"
          class="q-py-sm"
          @click="addItem"
        >
          <q-icon name="add" /> {{ $t('lists.add') }}
        </q-btn>
      </template>
    </q-input>
  </div>
</template>

<script>
import draggable from 'vuedraggable';
import {QList} from 'quasar';
import Vue from 'vue';

Vue.component('q-list', QList);

export default {
    name: "EditableList",
    components: { draggable },
    props: {
        value: {
            type: Array,
            default: () => []
        },
        inputIcon: {
            type: String,
            default: 'launch'
        },
        itemName: {
            type: String,
            default: function() { return this.$t('lists.default_item_name')}
        },
        allowDuplicates: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            addItemValue: '',
            editItemValue: '',
            itemUnderEdit: false
        }
    },
    methods: {
      addItem() {
        if (!this.addItemValue.length) {
            return;
        }
        if (!this.allowDuplicates && this.value.includes(this.addItemValue)) {
          this.addItemValue = '';
          return;
        }
        this.$emit('input', [...this.value, this.addItemValue]);
        this.addItemValue = '';
      },
      deleteItem(index) {
        this.$emit('input', [...this.value.slice(0, index), ...this.value.slice(index + 1)]);
      },
      editItem(index) {
          this.editItemValue = this.value[index];
          this.itemUnderEdit = index;
      },
      saveEdit() {
          const index = this.itemUnderEdit;
          if (index !== false && index < this.value.length) {
            this.$emit('input', [...this.value.slice(0, index), this.editItemValue, ...this.value.slice(index+1)]);
          }
          this.itemUnderEdit = false;
          this.editItemValue = '';
      },
      cancelEdit() {
          this.itemUnderEdit = false;
          this.editItemValue = '';
      },
      reorderItem(index, dir) {
        const newIndex = index + dir;

        if (newIndex === this.value.length || newIndex < 0) {
          return;
        }
        const startIndex = newIndex > index ? index : newIndex;
        const values = this.value.slice(startIndex, startIndex + 2);
        values.reverse();

        this.$emit('input', [...this.value.slice(0,startIndex), ...values, ...this.value.slice(startIndex +2)])


      }
    },
}
</script>

<style lang="sass">
.ghost
  opacity: 0.5
  background: #c8ebfb
.handle
  cursor: move

</style>