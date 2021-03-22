<template>
  <div class="q-col-gutter-md row">
    <q-input
      v-model.trim="addValue"
      outlined
      :label="label"
      class="col-md-5 col-12"
      @keyup.enter="addItem"
    >
      <template #after>
        <q-btn @click="addItem">
          <q-icon name="add" /> Add
        </q-btn>
      </template>
    </q-input>
    <div class="col-md-7 col-12">
      <q-chip
        v-for="item, index in value"
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
export default {
    name: 'TagList',
    props: {
        label: {
            type: String,
            default: ""
        },
        value: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            addValue: ''
        }
    },
    methods: {
        remove(index) {
            this.value.splice(index, 1);
        },
        addItem() {
            if (!this.addValue.length) {
                return;
            }
            this.value.push(this.addValue);
            this.addValue = '';
        }
    },
}
</script>
