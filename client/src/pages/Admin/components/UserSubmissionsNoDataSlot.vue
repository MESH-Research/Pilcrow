<template>
  <div class="col-grow">
    <div class="column items-center q-gutter-md">
      <div class="column items-center">
        <q-icon name="warning" size="50px" />
        <div class="text-weight-bold">
          No submissions matching your filters were found.
        </div>
      </div>
      <q-banner
        v-if="statusFilter.length == 0 || roleFilter.length == 0"
        rounded
        dense
        class="bg-yellow-4 text-black"
      >
        <template #avatar>
          <q-icon name="o_filter_alt" />
        </template>
        Head's up: Your filters will not return any results.
        <span v-if="statusFilter.length == 0"> (no status selected) </span>
        <span v-if="roleFilter.length == 0"> (no role selected) </span>
        <template #action>
          <q-btn label="Reset filters" flat @click="$emit('resetFilters')" />
        </template>
      </q-banner>
    </div>
  </div>
</template>

<script setup>
defineProps({
  dense: {
    type: Boolean,
    default: false
  },
  statusFilter: {
    type: Array,
    default: () => []
  },
  roleFilter: {
    type: Array,
    default: () => []
  }
})

defineEmits(["resetFilters"])
</script>
