<template>
  <q-select
    :input-style="{ paddingLeft: '0px' }"
    :label="$t(`submissions.invite_user.search.label`)"
    :loading="loading"
    :model-value="props.modelValue"
    :options="options"
    bottom-slots
    hide-dropdown-icon
    :fill-input="userIsSelected"
    outlined
    transition-hide="none"
    transition-show="none"
    use-input
    @filter="filterFn"
    @input-value="setInput"
    @update:model-value="onSelectUpdate"
  >
    <template #prepend>
      <q-icon color="accent" name="search" />
    </template>
    <template #no-option>
      <div class="text--grey q-mt-xs q-py-xs q-px-md">
        No user found. A brand new user will be added if you specify an
        <strong>email address</strong>.
      </div>
    </template>
    <template #hint>
      <div class="text--grey q-mt-xs">
        {{ $t("submissions.invite_user.search.hint") }}
      </div>
    </template>
    <template #selected-item="scope">
      <div v-if="scope.opt.username && scope.opt.email">
        <q-chip data-cy="selected_item" dense square class="q-ml-none">
          {{ scope.opt.username }} ({{ scope.opt.email }})
        </q-chip>
      </div>
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
      prop === null ||
      typeof prop === "object" ||
      typeof prop === "function" ||
      typeof prop === "string",
  },
})
const emit = defineEmits(["update:modelValue"])

const userIsSelected = computed(() => {
  console.log("check if selected")
  return typeof props.modelValue === "object" && props.modelValue !== null
})

function setInput(newValue) {
  if (!userIsSelected.value) {
    emit("update:modelValue", newValue)
  }
  return newValue
}

function onSelectUpdate(newValue) {
  console.log("select", newValue, props.modelValue)
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
