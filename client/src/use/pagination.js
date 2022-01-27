//TODO: This composable needs unit tests
import { unref, ref, toRef, toRefs, reactive, computed, readonly } from "vue"
import { defaults } from "lodash"
import { useQuery } from "@vue/apollo-composable"

export function usePagination(query, options) {
  const opts = defaults(unref(options) || {}, {
    variables: {},
  })

  const vars = reactive(Object.assign({ page: 1 }, opts.variables))

  const itemData = ref([])
  const paginatorInfo = reactive({
    count: 0,
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
  })

  const queryReturn = useQuery(query, vars)

  queryReturn.onResult((result) => {
    if (result.loading) {
      return
    }
    itemData.value = extractElement(result.data, "data")
    Object.assign(paginatorInfo, extractElement(result.data, "paginatorInfo"))
  })

  function updatePage(newValue) {
    vars.page = newValue
  }

  const binds = computed(() => ({
    modelValue: vars.page,
    min: 1,
    max: paginatorInfo.lastPage,
  }))

  const listeners = {
    "update:modelValue": updatePage,
  }

  return {
    data: itemData,
    currentPage: readonly(toRef(vars, "page")),
    updatePage,
    paginatorInfo: toRefs(paginatorInfo),
    binds,
    listeners,
    ...queryReturn,
  }
}

function extractElement(data, element) {
  const keys = Object.keys(data)
  if (keys.length !== 1) {
    throw "Unable to extract query return (Are you sure this is a paginated query?)"
  }
  return data[keys[0]][element]
}
