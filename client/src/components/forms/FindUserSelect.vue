<template>
  <q-select
    :model-value="props.modelValue"
    :options="options"
    bottom-slots
    hide-dropdown-icon
    label="User to Add"
    outlined
    transition-hide="none"
    transition-show="none"
    use-input
    :loading="loading"
    @update:model-value="onSelectUpdate"
    @filter="filterFn"
  >
    <template #prepend>
      <q-icon color="accent" name="search" />
    </template>
    <template #hint>
      <div class="text--grey q-mt-xs">Search by username, email, or name.</div>
    </template>
    <template #selected-item="scope">
      <q-chip data-cy="selected_item" dense square>
        {{ scope.opt.username }} ({{ scope.opt.email }})
      </q-chip>
    </template>
    <template #option="scope">
      <q-item data-cy="options_item" v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label>
            {{ scope.opt.username }} ({{ scope.opt.email }})
          </q-item-label>
          <q-item-label v-if="scope.opt.name" caption class="text--grey">
            {{ scope.opt.name }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>
    <template v-for="(_, name) in $slots" #[name]="slotData">
      <slot :name="name" v-bind="{ ...slotData }" />
    </template>
  </q-select>
</template>

<script setup>
import { useQuery } from "@vue/apollo-composable"
import { SEARCH_USERS } from "src/graphql/queries"
import { ref, computed } from "vue"
const props = defineProps({
  modelValue: {
    default: null,
    validator: (prop) =>
      prop === null || typeof prop === "object" || typeof prop === "function",
  },
})
const emit = defineEmits(["update:modelValue"])

function onSelectUpdate(newValue) {
  emit("update:modelValue", newValue)
}

const variables = ref({ term: "" })
const { result, loading, refetch } = useQuery(SEARCH_USERS, variables)

const options = computed(() => {
  return result.value?.userSearch.data ?? []
})

async function filterFn(val, update) {
  variables.value = { term: val }
  refetch()

  //Immediately call the update function to clear the loading flag for the filter
  //The loading ref on the query will indicate loading to the user.
  update(() => {})
}
</script>
