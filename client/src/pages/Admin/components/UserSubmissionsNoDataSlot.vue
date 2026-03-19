<template>
  <div class="col-grow">
    <div class="column items-center q-gutter-md">
      <div class="full-width row flex-center text-grey-7 q-gutter-sm q-py-lg">
        <q-icon size="2em" name="assignment" />
        <span>{{ $t("admin.users.details.no_submissions") }}</span>
      </div>
      <q-banner
        v-if="statusFilter.length === 0 || roleFilter.length === 0"
        rounded
        dense
        class="bg-yellow-4 text-black"
      >
        <template #avatar>
          <q-icon name="o_filter_alt" />
        </template>
        Head's up: Your filters will not return any results.
        <span v-if="statusFilter.length === 0"> (no status selected) </span>
        <span v-if="roleFilter.length === 0"> (no role selected) </span>
        <template #action>
          <q-btn label="Reset filters" flat @click="$emit('resetFilters')" />
        </template>
      </q-banner>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  dense?: boolean
  statusFilter?: string[]
  roleFilter?: string[]
}

withDefaults(defineProps<Props>(), {
  dense: false,
  statusFilter: () => [],
  roleFilter: () => []
})

interface Emits {
  resetFilters: []
}
defineEmits<Emits>()
</script>
