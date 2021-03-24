<template>
  <div class="q-col-gutter-md row">
    <q-input
      v-model.trim="addValue"
      outlined
      :label="$t('lists.new', [itemName])"
      class="col-md-5 col-12"
      @keydown.enter.prevent="addItem"
    >
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
        itemName: {
            type: String,
            default: function() {return this.$t('lists.default_item_name')}
        },
        value: {
            type: Array,
            default: () => []
        },
        allowDuplicates: {
          type: Boolean,
          default: false

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
            if  (!this.allowDuplicates && this.value.includes(this.addValue)) {
              this.addValue = '';
              return;
            }
            this.value.push(this.addValue);
            this.addValue = '';
        }
    },
}
</script>
