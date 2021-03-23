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
          >
            <template
              #after
              class="q-gutter-md"
            >
              <q-btn
                dense
                aria-label="Save Item Edit"
                @click="saveEdit"
              >
                <q-icon name="check" /> Save
              </q-btn>
              <q-btn
                aria-label="Cancel Item Edit"
                dense
                @click="cancelEdit"
              >
                <q-icon name="close" /> Cancel
              </q-btn>
            </template>
          </q-input>
        </q-item-section>
        <q-item-section
          side
          style="flex-direction: row;"
        >
          <q-btn
            flat
            dense
            icon="edit"
            aria-label="Edit Item"
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
            aria-label="Move Item Up"
            @click="reorderItem(index, -1)"
          />
          <q-btn
            :disabled="
              index === value.length - 1"
            flat
            dense
            icon="arrow_downward"
            aria-label="Move Item Down"
            @click="reorderItem(index, 1)"
          />
          <q-btn
            flat
            dense
            icon="delete"
            aria-label="Delete Item"
            @click="deleteItem(index)"
          />
        </q-item-section>
      </q-item>
    </draggable>
    <q-input
      v-model="addItemValue"
      :label="inputLabel"
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
          <q-icon name="add" /> {{ $t('add') }}
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
        inputLabel: {
            type: String,
            default: 'Enter New Item'
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