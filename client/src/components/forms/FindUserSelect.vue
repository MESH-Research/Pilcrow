<template>
  <q-form class="col" @submit="handleSubmit">
    <q-select
      ref="qSelectRef"
      v-model="modelValue"
      :input-style="{ paddingLeft: '0px' }"
      :label="$t(`submissions.invite_user.search.label`)"
      :loading="loading"
      :options="options"
      bottom-slots
      hide-dropdown-icon
      outlined
      transition-hide="none"
      transition-show="none"
      use-input
      @input-value="(val: string) => (inputValue = val)"
      @filter="filterFn"
    >
      <template #after>
        <q-btn
          :ripple="{ center: true }"
          color="accent"
          data-cy="button-assign"
          :label="$t(`publication.setup_pages.assign`)"
          type="submit"
          stretch
          @click="handleSubmit"
        />
      </template>
      <template #prepend>
        <q-icon color="accent" name="search" />
      </template>
      <template #no-option>
        <div class="text--grey q-mt-xs q-py-xs q-px-md">
          <span v-if="canInvite">
            {{ $t("submissions.invite_user.search.invite") }}
          </span>
          <span v-else>
            {{ $t("submissions.invite_user.search.no_user") }}
          </span>
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
    </q-select>
  </q-form>
</template>

<script setup lang="ts">
import type { SearchUsersQuery } from "src/gql/graphql"
import { SearchUsersDocument } from "src/gql/graphql"
import { QSelect } from "quasar"

export type SearchUsersSelected = SearchUsersQuery["userSearch"]["data"][number]

export interface NewStagedUser {
  email: string
}

interface Props {
  canInvite?: boolean
}

const { canInvite = false } = defineProps<Props>()

interface Emits {
  (e: "add", user: SearchUsersSelected): void
  (e: "invite", user: NewStagedUser): void
}

const emit = defineEmits<Emits>()
const modelValue = ref<SearchUsersSelected>()
const inputValue = ref<string | null>()

const qSelectRef = useTemplateRef<InstanceType<typeof QSelect>>("qSelectRef")

const variables = ref({ term: "" })
const { result, loading, refetch } = useQuery(SearchUsersDocument, variables)

const options = computed(() => {
  return result.value?.userSearch.data ?? []
})

function filterFn(val, update) {
  variables.value = { term: val }
  void refetch()

  //Immediately call the update function to clear the loading flag for the filter
  //The loading ref on the query will indicate loading to the user.
  update(() => {})
}

function handleSubmit() {
  if (modelValue.value) {
    emit("add", modelValue.value)
  } else if (inputValue.value && canInvite) {
    emit("invite", { email: inputValue.value })
  }
}

defineExpose({
  reset: () => {
    qSelectRef.value?.reset()
    modelValue.value = undefined
  }
})
</script>

<script lang="ts">
graphql(`
  query SearchUsers($term: String, $page: Int) {
    userSearch(term: $term, page: $page) {
      paginatorInfo {
        ...PaginationFields
      }
      data {
        id
        username
        name
        email
      }
    }
  }
`)
</script>
