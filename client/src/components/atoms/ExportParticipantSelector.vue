<template>
  <q-card square bordered flat>
    <q-card-section>
      <h4 class="q-my-none text-bold">
        {{ $t("export.participants.title") }}
      </h4>
      <div v-if="all_commenters.length == 0">
        <p>{{ $t("export.participants.no_commenters") }}</p>
      </div>
      <div v-else>
        <p>{{ $t("export.participants.byline") }}</p>
        <div class="q-gutter-sm q-mb-sm">
          <q-btn
            size="sm"
            :label="$t('export.participants.select_all')"
            @click="updateSelected([...all_commenters])"
          />
          <q-btn
            size="sm"
            :label="$t('export.participants.select_none')"
            @click="updateSelected([])"
          />
        </div>
        <q-list>
          <q-item
            v-for="commenter in all_commenters"
            :key="commenter.id"
            tag="label"
          >
            <q-item-section avatar>
              <q-checkbox
                v-model="selectedModel"
                :aria-labelledby="`commenter_label_${commenter.id}`"
                :val="commenter"
              />
            </q-item-section>
            <q-item-section top avatar>
              <avatar-image :user="commenter" rounded />
            </q-item-section>
            <q-item-section>
              <q-item-label :id="`commenter_label_${commenter.id}`">{{
                commenter.display_label
              }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
    </q-card-section>
  </q-card>
</template>
<script setup>
import AvatarImage from "./AvatarImage.vue"
import { computed, ref, watch } from "vue"
import { GET_SUBMISSION_COMMENTERS } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"

const props = defineProps({
  submissionId: {
    type: String,
    required: true
  },
  commenterType: {
    type: String,
    default: null
  }
})
const emit = defineEmits(["update:modelValue"])
const selected = ref([])

const queryVars = computed(() => ({
  id: props.submissionId,
  commenterType: props.commenterType
}))
const { result } = useQuery(GET_SUBMISSION_COMMENTERS, queryVars)
const all_commenters = computed(
  () => result.value?.submission?.commenters ?? []
)

const selectedModel = computed({
  get: () => selected.value,
  set: (val) => updateSelected(val)
})

function updateSelected(val) {
  selected.value = val
  emit("update:modelValue", val)
}

// Reset selection when comment type changes
watch(
  () => props.commenterType,
  () => updateSelected([])
)

// Auto-populate when commenters load and nothing is selected
watch(
  all_commenters,
  (newCommenters) => {
    if (selected.value.length === 0 && newCommenters.length > 0) {
      updateSelected([...newCommenters])
    }
  },
  { immediate: true }
)
</script>
