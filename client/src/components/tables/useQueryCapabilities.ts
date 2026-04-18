import { computed, toValue, type MaybeRef } from "vue"
import type { DocumentNode, OperationDefinitionNode } from "graphql"

export function useQueryCapabilities(query: MaybeRef<DocumentNode>) {
  const queryVariables = computed(() => {
    const doc = toValue(query)
    const opDef = doc.definitions?.find(
      (d): d is OperationDefinitionNode => d.kind === "OperationDefinition"
    )
    return opDef?.variableDefinitions?.map((d) => d.variable.name.value) ?? []
  })

  const searchable = computed(() => queryVariables.value.includes("search"))

  const rowsPerPageOptions = computed(() =>
    queryVariables.value.includes("first") ? [10, 25, 50, 100] : []
  )

  const enablePagination = computed(() => queryVariables.value.includes("page"))

  return { queryVariables, searchable, rowsPerPageOptions, enablePagination }
}
