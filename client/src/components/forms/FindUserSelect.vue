<template>
  <q-select
    :model-value="props.modelValue"
    :options="options"
    bottom-slots
    hide-dropdown-icon
    input-debounce="50"
    label="User to Assign"
    outlined
    transition-hide="none"
    transition-show="none"
    use-input
    @update:model-value="onSelectUpdate"
    @filter="filterFn"
  >
    <template #hint>
      <div class="text--grey">Search by username, email, or name.</div>
    </template>
    <template #selected-item="scope">
      <q-chip :data-cy="props.cySelectedItem" dense square>
        {{ scope.opt.username }} ({{ scope.opt.email }})
      </q-chip>
    </template>
    <template #option="scope">
      <q-item :data-cy="props.cyOptionsItem" v-bind="scope.itemProps">
        <q-item-section>
          <q-item-label>
            {{ scope.opt.username }} ({{ scope.opt.email }})
          </q-item-label>
          <q-item-label v-if="scope.opt.name" caption class="text-grey-10">
            {{ scope.opt.name }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </template>
  </q-select>
</template>

<script setup>
import { useQuery } from "@vue/apollo-composable"
import { SEARCH_USERS } from "src/graphql/queries"
import { ref } from "vue"
const props = defineProps({
  modelValue: {
    default: null,
    validator: (prop) =>
      prop === null || typeof prop === "object" || typeof prop === "function",
  },
  cySelectedItem: {
    type: String,
    default: "",
  },
  cyOptionsItem: {
    type: String,
    default: "",
  },
})

const emit = defineEmits(["update:modelValue"])

function onSelectUpdate(newValue) {
  emit("update:modelValue", newValue)
}

const options = ref([])
const { refetch } = useQuery(SEARCH_USERS)

async function filterFn(val, update) {
  const newData = await refetch({ term: val.toLowerCase() })
  const newOptions = newData.data.userSearch.data

  update(() => {
    options.value = newOptions
  })
}
</script>
