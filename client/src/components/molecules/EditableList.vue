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
          <q-item-label v-if="itemUnderEdit !== index">
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
                @click="saveEdit"
              >
                <q-icon name="check" /> Save
              </q-btn>
              <q-btn
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
            @click="reorderItem(index, -1)"
          />
          <q-btn
            :disabled="
              index === value.length - 1"
            flat
            dense
            icon="arrow_downward"
            @click="reorderItem(index, 1)"
          />
          <q-btn
            flat
            dense
            icon="delete"
            @click="deleteItem(index)"
          />
        </q-item-section>
      </q-item>
    </draggable>
    <q-input
      v-model="addItemValue"
      label="Add Website"
      outlined
      @keyup.enter="addItem"
    >
      <template
        v-if="inputIcon"
        #prepend
      >
        <q-icon :name="inputIcon" />
      </template>
      <template #after>
        <q-btn
          class="q-py-sm"
          @click="addItem"
        >
          <q-icon name="add" /> Add
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
        this.value.push(this.addItemValue);
        this.addItemValue = '';
      },
      deleteItem(index) {
        this.value.splice(index, 1);
      },
      editItem(index) {
          this.editItemValue = this.value[index];
          this.itemUnderEdit = index;
      },
      saveEdit() {
          this.value.splice(this.itemUnderEdit, 1, this.editItemValue);
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
        const swap = this.value[newIndex];
        this.value.splice(newIndex, 1, this.value[index]);
        this.value.splice(index, 1, swap);

      }
    },
}
</script>

<style lang="sass">
.ghost
  opacity: 0.5
  background: #c8ebfb

</style>