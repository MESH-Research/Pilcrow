<template>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-pa-md">
    <div class="column q-gutter-md">
      <publication-style-criteria :publication="publication" />
    </div>
  </article>
</template>

<script setup>
import { GET_PUBLICATION } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import PublicationStyleCriteria from "src/components/PublicationStyleCriteria.vue"
import { computed } from "vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, { id: props.id })
const publication = computed(() => {
  return result.value?.publication ?? null
})
</script>
