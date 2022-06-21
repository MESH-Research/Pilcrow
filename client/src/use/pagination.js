//TODO: This composable needs unit tests
import { unref, reactive, computed } from "vue"
import { defaults } from "lodash"
import { useQuery } from "@vue/apollo-composable"

export function usePagination(doc, options) {
  const opts = defaults(unref(options) || {}, {
    variables: {},
  })

  const vars = reactive(Object.assign({ page: 1 }, opts.variables))

  const query = useQuery(doc, vars)

  const itemData = computed(() => {
    if (query.loading.value) return []
    return extractElement(query.result.value, "data")
  })

  const paginatorInfo = computed(() => {
    return query.loading.value
      ? {}
      : extractElement(query.result.value, "paginatorInfo")
  })

  function updatePage(newValue) {
    console.log(vars)
    console.log(newValue)
    vars.page = newValue
    console.log(vars)
  }

  const binds = computed(() => ({
    modelValue: vars.page,
    min: 1,
    max: paginatorInfo.value?.lastPage ?? 1,
  }))

  const listeners = {
    "update:modelValue": updatePage,
  }

  return {
    data: itemData,
    updatePage,
    paginatorInfo,
    binds,
    listeners,
    query: query,
    vars,
  }
}

function extractElement(data, element) {
  const keys = Object.keys(data)
  if (keys.length !== 1) {
    throw "Unable to extract query return (Are you sure this is a paginated query?)"
  }
  return data[keys[0]][element]
}
